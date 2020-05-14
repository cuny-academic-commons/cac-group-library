<template>
	<div class="library-item group-library-row">
		<div class="group-library-item-title">
			<div class="group-library-item-icon">
				<img
					class="group-library-item-icon-img"
					:src="iconSrc()" />
			</div>

			<div class="group-library-item-title-details">
				<a
					:href="url()"
					class="group-library-item-title-title"
				>{{ title() }}</a>

				<p
					class="library-item-description"
					v-if="showDescription()"
				>
					<span v-if="isForumAttachment()">
						In topic <a :href="topicUrl()">{{ topicTitle() }}</a>
					</span>

					<span v-else>
						{{ description() }}
					</span>
				</p>
			</div>
		</div>

		<div class="group-library-item-added-by">
			<a :href="addedByUrl()">{{ addedByName() }}</a>
		</div>

		<div class="group-library-item-date">
			{{ date() }}
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
				const dtf = new Intl.DateTimeFormat('en-US').format( d )
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

			showDescription() {
				return ( this.isForumAttachment() || this.description().length > 0 ) && this.$store.state.showDescriptions
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

.group-library-item-title,
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
	display: none;
	position: absolute;
	right: 10px;
}

.group-library-items > ul > li {
	border-bottom: 1px solid #ddd;
}

.group-library-items > ul > li:last-child {
	border-bottom: none;
}

.group-library-row:hover .group-library-edit {
	display: block;
}
</style>
