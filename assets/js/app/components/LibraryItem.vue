<template>
	<div class="library-item group-library-row">
		<div class="group-library-item-title">
			<div class="group-library-item-icon">
				<img
					:class="iconClass()"
					:src="iconSrc()" />
			</div>

			<div class="group-library-item-title-details">
				<a
					:href="url()"
					class="group-library-item-title-title"
				>{{ title() }}</a>

				<div
					class="item-folders"
					v-if="showFolders()"
				>
					<span class="folder-icon">
						<img
							:src="folderIconUrl"
						/>
					</span>

					<a
						class="item-folder-link"
						v-for="folder in itemFolders()"
						v-on:click="onFolderClick(folder)"
					>{{folder}}</a>
				</div>
			</div>
		</div>

		<div class="group-library-item-details">
			<span v-if="isForumAttachment()">
				In topic <a :href="topicUrl()">{{ topicTitle() }}</a>
			</span>

			<span v-else>
				{{ description() }}
			</span>
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
</template>

<script>
	export default {
		computed: {
			folderIconUrl() {
				const { imgUrlBase } = window.CACGroupLibrary;
				return imgUrlBase + 'folder-icon.png'
			},
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

			showDescription() {
				return ( this.isForumAttachment() || this.description().length > 0 ) && this.$store.state.showDescriptions
			},

			showFolders() {
				const theItem = this.getItem()
				const currentFolder = this.$store.state.route.query.hasOwnProperty( 'folder' ) ? decodeURIComponent( this.$store.state.route.query.folder ) : 'any'

				return this.$store.state.showDescriptions && 'any' === currentFolder && theItem.hasOwnProperty( 'folders' ) && theItem.folders.length > 0
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
						fileName = 'bp-doc.png'
					break

					case 'bp_group_document' :
						fileName = this.getFileTypeBase() + '.png'
					break

					case 'cacsp_paper' :
						fileName = 'papers.png'
					break

					case 'external_link' :
						fileName = this.getServiceFromUrl() + '.png'
					break

					case 'forum_attachment' :
						fileName = this.getFileTypeBase() + '-attachment.png'
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
.library-item {
	padding: 12px;
}

.group-library-item-icon {
	float: left;
}

.group-library-item-icon-img {
	height: 16px;
	width: 16px;
}

.group-library-item-icon-img.group-library-item-icon-img-zoom {
	height: 20px;
	margin-left: -2px;
	width: 20px;
}

.group-library-item-title,
.group-library-item-date,
.group-library-item-added-by {
	margin-right: 16px;
}

.group-library-item-title-title {
	font-weight: 700;
}

.group-library-item-title-details {
	margin-left: calc(12px + 16px);
}

.library-item-description {
	margin-top: 6px;
}

.group-library-edit {
	position: absolute;
	right: 10px;
}

.group-library-items > ul > li {
	border-bottom: 1px solid #ddd;
}

.item-folders {
	margin-top: 10px;
}

.item-folders .folder-icon {
	line-height: 16px;
	margin-right: 3px;
}

.item-folders .folder-icon img {
	height: 14px;
	vertical-align: text-bottom;
}

a.item-folder-link {
	cursor: pointer;
	text-decoration: underline;
}

a.item-folder-link:hover {
	text-decoration: none;
}
</style>
