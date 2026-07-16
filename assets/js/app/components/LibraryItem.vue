<template>
	<span>
		<div class="library-item group-library-row" role="row">
			<div class="group-library-item-toggle" role="cell">
				<button
					class="drawer-toggle-button"
					:class="{ 'drawer-toggle-open': isDrawerOpen }"
					@click="toggleDrawer()"
					:aria-expanded="isDrawerOpen"
					:aria-label="isDrawerOpen ? `Collapse details for ${title()}` : `Expand details for ${title()}`"
				>
					<svg class="drawer-toggle-icon" viewBox="0 0 32 32" fill="none">
						<path d="M13.8459 9.30324L19.5292 15.3442C19.7279 15.543 19.8074 15.7814 19.8074 15.9801C19.8074 16.2186 19.7279 16.457 19.569 16.6558L13.8459 22.6968C13.4882 23.0942 12.8921 23.0942 12.4946 22.7365C12.0972 22.3788 12.0972 21.7827 12.4549 21.3852L17.5421 15.9801L12.4549 10.6148C12.0972 10.2173 12.0972 9.62119 12.4946 9.2635C12.8921 8.90581 13.4882 8.90581 13.8459 9.30324Z" fill="#A1A1A1"/>
					</svg>
				</button>
			</div>
			<div class="group-library-item-title" role="cell">
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

			<div class="group-library-item-details" role="cell">
				<p v-if="isForumAttachment()">
					In topic <a :href="topicUrl()">{{ topicTitle() }}</a>
				</p>

				<p v-else>
					{{ description() }}
				</p>
			</div>

			<div class="group-library-item-tagged" role="cell">
				<a
					class="item-folder-link"
					v-for="folder in itemFolders()"
					@click.prevent="onFolderClick(folder)"
					:href="'?folder=' + encodeURIComponent(folder)"
				><span>{{folder}}</span></a>
			</div>

			<div class="group-library-item-date" role="cell">
				{{ date() }}
			</div>

			<div class="group-library-item-added-by" role="cell">
				<a :href="addedByUrl()">{{ addedByName() }}</a>
			</div>

		</div>

		<div
			v-if="isDrawerOpen"
			class="group-library-item-drawer-row"
			role="row"
		>
			<div class="group-library-item-drawer-cell" role="cell">
				<ItemDetailsDrawer
					:itemId="Number(itemId)"
				/>
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
				v-if="itemHasFolders()"
			>
				Tagged: <a
					class="item-folder-link"
					v-for="folder in itemFolders()"
					v-on:click="onFolderClick(folder)"
				>{{folder}}</a>
			</p>
		</div>
	</span>
</template>

<script>
	import ItemDetailsDrawer from './ItemDetailsDrawer.vue'

	export default {
		components: {
			ItemDetailsDrawer
		},

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
				isMenuOpen: false,
				isDrawerOpen: false
			}
		},

		methods: {
			addedByName() {
				return this.getItem().user.nameWithoutPronouns
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

			itemHasFolders() {
				const item = this.getItem()
				return item.hasOwnProperty( 'folders' ) && item.folders.length > 0
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

			toggleDrawer() {
				this.isDrawerOpen = ! this.isDrawerOpen
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

	.group-library-item-tagged {
		display: none;
	}

	.group-library-row.group-library-item-details-mobile {
		display: block;
	}
}

.library-item {
	padding: 12px;
}

.group-library-item-toggle {
	flex: 0 0 24px;
	display: flex;
	align-items: center;
}

.drawer-toggle-button {
	background: none;
	border: none;
	cursor: pointer;
	padding: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	width: 24px;
	height: 24px;
	transition: transform 0.2s ease;
}

.drawer-toggle-button:hover {
	opacity: 0.7;
}

.drawer-toggle-button.drawer-toggle-open {
	transform: rotate(90deg);
}

.drawer-toggle-icon {
	flex: 0 0 32px;
	width: 32px;
	height: 32px;
	color: #555;
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
	text-decoration: none;
}

a.item-folder-link span {
	text-decoration: underline;
}

a.item-folder-link:not(:last-child)::after {
	content: ", ";
}

a.item-folder-link:hover span {
	text-decoration: none;
}

.drawer-field {
	a.item-folder-link {
		background: white;
		border: 1px solid #d8d8d8;
		border-radius: 4px;
		color: var(--black);
		cursor: pointer;
		display: inline-block;
		font-size: 14px;
		margin-right: 4px;
		padding: 4px 6px;
		text-decoration: none;
	}

	a.item-folder-link:hover {
		background: var(--light-grey);
	}
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
