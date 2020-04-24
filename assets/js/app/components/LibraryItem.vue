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

			date() {
				const d = new Date( this.getItem().date_modified )
				const dtf = new Intl.DateTimeFormat('en-US').format( d )
				return dtf
			},

			description() {
				return this.getItem().description
			},

			fileType() {
				return this.getItem().file_type
			},

			getItem() {
				return this.$store.state.libraryItems[ this.itemId ]
			},

			isForumAttachment() {
				return 'forum_attachment' === this.itemType()
			},

			showDescription() {
				return this.$store.state.showDescriptions
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

					case 'forum_attachment' :
						fileName = this.getFileTypeBase() + '-attachment.png'
					break
				}

				return window.CACGroupLibrary.iconUrlBase + fileName
			},

			itemType() {
				return this.getItem().item_type
			},

			title() {
				return this.getItem().title
			},

			topicTitle() {
				return this.getItem().topic_title
			},

			topicUrl() {
				return this.getItem().topic_url
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

.group-library-item-title-details {
	margin-left: calc(12px + 16px);
}

.library-item-description {
	margin-top: 6px;
}
</style>
