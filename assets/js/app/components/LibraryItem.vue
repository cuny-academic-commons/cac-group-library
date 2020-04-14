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
					v-if="hasDescription()"
				>
					{{ description() }}
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

			hasDescription() {
				return this.description().length > 0
			},

			iconSrc() {
				let fileName

				switch ( this.itemType() ) {
					case 'bp_doc' :
						fileName = 'bp-doc.png'
					break

					case 'bp_group_document' :
						switch ( this.fileType() ) {
							case 'pdf' :
								fileName = 'pdf.png'
							break

							case 'xls' :
							case 'xlsx' :
								filename = 'excel.png'
							break

							case 'doc' :
							case 'docx' :
								filename = 'word.png'
							break

							case 'ppt' :
							case 'pptx' :
								filename = 'word.png'
							break

							default :
								filename = 'general.png'
							break
						}
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
</style>
