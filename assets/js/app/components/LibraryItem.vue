<template>
	<span>
		<div class="library-item group-library-row">
			<div class="group-library-item-title">
				<div class="group-library-item-icon">
					<img
						:class="iconClass()"
						:src="iconSrc()" 
						alt="" />
				</div>

				<div class="group-library-item-title-details">
					<a
						:href="url()"
						class="group-library-item-title-title"
					>{{ title() }}</a>

					<div
						class="item-file-size"
						v-if="fileSize().length > 0"
					>
						{{ fileSize() }}
					</div>
				</div>
			</div>

			<div class="group-library-item-details">
				<p v-if="isForumAttachment()">
					In topic <a :href="topicUrl()">{{ topicTitle() }}</a>
				</p>

				<p v-else>
					{{ description() }}
				</p>

				<p
					class="item-folders"
					v-if="showFolders()"
				>
					Tagged in: <a
						class="item-folder-link"
						v-for="folder in itemFolders()"
						v-on:click="onFolderClick(folder)"
					>{{folder}}</a>
				</p>
			</div>

			<div class="group-library-item-date">
				{{ date() }}
			</div>

			<div class="group-library-item-added-by">
				<a :href="addedByUrl()">{{ addedByName() }}</a>
			</div>

			<div
				class="group-library-edit"
				v-if="canEdit()"
			>
				<button
					class="group-library-item-menu-toggle"
					v-on:click="toggleMenu()"
					aria-haspopup="true"
					:aria-controls="'group-library-item-menu-' + itemId"
					:aria-expanded="isMenuOpen"
				><img
					:src="moreIconUrl"
					alt="Click for advanced options"
				/></button>

				<div
					v-show="isMenuOpen"
					class="group-library-item-menu"
					:id="'group-library-item-menu-' + itemId"
				>
					<a
						:href="editUrl()"
						v-if="editLinkIsStatic()"
					>Edit</a>

					<router-link
						:to="'/edit/' + itemId"
						v-else
					>Edit</router-link>
				</div>
			</div>
		</div>

		<div class="group-library-item-details-mobile group-library-row">
			<p v-if="isForumAttachment()">
				In topic <a :href="topicUrl()">{{ topicTitle() }}</a>
			</p>

			<p v-else>
				{{ description() }}
			</p>

			<p
				class="item-folders"
				v-if="showFolders()"
			>
				Tagged in: <a
					class="item-folder-link"
					v-for="folder in itemFolders()"
					v-on:click="onFolderClick(folder)"
				>{{folder}}</a>
			</p>
		</div>
	</span>
</template>

<script>
	export default {
		computed: {
			folderIconUrl() {
				const { imgUrlBase } = window.CACGroupLibrary;
				return imgUrlBase + 'folder-icon.png'
			},

			moreIconUrl() {
				const { imgUrlBase } = window.CACGroupLibrary;
				return imgUrlBase + 'more.png'
			}
		},

		data() {
			return {
				isMenuOpen: false
			}
		},

		methods: {
			addedByName() {
				return this.getItem().user.name
			},

			addedByUrl() {
				return this.getItem().user.url
			},

			canEdit() {
				const { can_edit } = this.getItem()
				return !! can_edit
			},

			date() {
				const { date_modified } = this.getItem()

				// JS format.
				const dateString = date_modified.replace( ' ', 'T' )
				const d = new Date( dateString )
				const dateTimeOptions = {
					month: 'short',
					day: 'numeric',
					year: 'numeric'
				}
				const dtf = new Intl.DateTimeFormat( 'en-US', dateTimeOptions ).format( d )
				return dtf
			},

			description() {
				const item = this.getItem()
				return item.hasOwnProperty( 'description' ) ? item.description : ''
			},

			editLinkIsStatic() {
				return this.editUrl().length > 0
			},

			editUrl() {
				const item = this.getItem()
				return item.hasOwnProperty( 'edit_url' ) ? item.edit_url : ''
			},

			fileSize() {
				const item = this.getItem()
				return item.hasOwnProperty( 'file_size' ) ? item.file_size : ''
			},

			fileType() {
				const item = this.getItem()
				return item.hasOwnProperty( 'file_type' ) ? item.file_type : ''
			},

			getItem() {
				return this.$store.state.libraryItems[ this.itemId ]
			},

			isForumAttachment() {
				return 'forum_attachment' === this.itemType()
			},

			itemFolders() {
				return this.getItem().folders
			},

			onFolderClick(folder) {
				let folderQuery = Object.assign( {}, this.$route.query )

				folderQuery.page = 1

				let folderQueryArgs
				if ( '' === folder ) {
					delete folderQuery.folder
				} else {
					folderQuery.folder = encodeURIComponent( folder )
				}

				this.$router.push( {
					path: '/',
					query: folderQuery
				} )

				this.$store.commit( 'refresh' )
			},

			showFolders() {
				const theItem = this.getItem()
				const currentFolder = this.$store.state.route.query.hasOwnProperty( 'folder' ) ? decodeURIComponent( this.$store.state.route.query.folder ) : 'any'

				return 'any' === currentFolder && theItem.hasOwnProperty( 'folders' ) && theItem.folders.length > 0
			},

			getFileTypeBase() {
				let fileName

				switch ( this.fileType() ) {
					case 'pdf' :
						fileName = 'pdf'
					break

					case 'xls' :
					case 'xlsx' :
						fileName = 'excel'
					break

					case 'doc' :
					case 'docx' :
						fileName = 'word'
					break

					case 'ppt' :
					case 'pptx' :
						fileName = 'powerpoint'
					break

					case 'mp3' :
						fileName = 'audio'
					break

					case 'jpg' :
					case 'jpeg' :
					case 'gif' :
					case 'bmp' :
					case 'png' :
					case 'svg' :
					case 'tif' :
						fileName = 'image'
					break

					default :
						fileName = 'general'
					break
				}

				return fileName
			},

			iconClass() {
				let classes = [ 'group-library-item-icon-img' ]

				if ( this.itemType() === 'external_link' ) {
					classes.push( 'group-library-item-icon-img-' + this.getServiceFromUrl() )
				}

				return classes.join( ' ' )
			},

			iconSrc() {
				let fileName

				switch ( this.itemType() ) {
					case 'bp_doc' :
					case 'cacsp_paper' :
						fileName = 'general.svg'
					break

					case 'forum_attachment' :
					case 'bp_group_document' :
						fileName = this.getFileTypeBase() + '.svg'
					break

					case 'external_link' :
						fileName = this.getServiceFromUrl() + '.svg'
					break
				}

				return window.CACGroupLibrary.iconUrlBase + fileName
			},

			getServiceFromUrl() {
				const el = document.createElement('a')
				el.href = this.url()

				if ( el.hostname.endsWith( '.dropbox.com' ) ) {
					return 'dropbox'
				}

				if ( el.hostname.endsWith( '.zoom.com' ) || el.hostname.endsWith( '.zoom.us' ) ) {
					return 'zoom'
				}

				if ( 'docs.google.com' === el.hostname || 'drive.google.com' === el.hostname ) {
					return 'drive'
				}

				if ( '1drv.ms' === el.hostname || 'onedrive.live.com' === el.hostname ) {
					return 'onedrive'
				}

				return 'external'
			},

			itemType() {
				return this.getItem().id
			},

			itemType() {
				return this.getItem().item_type
			},

			title() {
				return this.getItem().title
			},

			toggleMenu() {
				this.isMenuOpen = ! this.isMenuOpen
			},

			topicTitle() {
				const item = this.getItem()
				return item.hasOwnProperty( 'topic_title' ) ? item.topic_title : ''
			},

			topicUrl() {
				const item = this.getItem()
				return item.hasOwnProperty( 'topic_url' ) ? item.topic_url : ''
			},

			url() {
				return this.getItem().url
			}
		},

		props: [
			'itemId'
		]
	}
</script>

<style>
.group-library-row.group-library-item-details-mobile {
	display: none;
}

@media (max-width: 768px) {
	.group-library-item-details {
		display: none;
	}

	.group-library-row.group-library-item-details-mobile {
		display: block;
	}
}

.library-item {
	padding: 12px;
}

.group-library-item-icon {
	display: flex;
	flex: 0 0 40px;
	align-items: center;
	justify-content: center;
	height: 40px;
	width: 40px;
	border-radius: 50%;
	background: #367BA31A;
}

.group-library-item-icon-img {
	height: 16px;
}

.group-library-item-icon-img.group-library-item-icon-img-zoom {
	height: 20px;
	margin-left: -2px;
	width: 20px;
}

.group-library-item-title-title {
	font-weight: 500;
}

.group-library-edit {
	flex: 0 0 40px;
	position: relative;
}

.group-library-item-menu {
	background: #fff;
	border: 1px solid var(--med-dark-grey);
	padding: 12px;
	position: absolute;
	top: 37px;
}

a.item-folder-link {
	cursor: pointer;
	text-decoration: underline;
}

a.item-folder-link:hover {
	text-decoration: none;
}

button.group-library-item-menu-toggle {
	background: none;
	border: 1px solid transparent;
	cursor: pointer;
	padding: 0;
}

button.group-library-item-menu-toggle:hover {
	border-color: var(--med-grey);
}

button.group-library-item-menu-toggle[aria-expanded="true"] {
	border-color: var(--med-dark-grey);
}

</style>
