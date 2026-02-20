<template>
	<div class="item-details-drawer">
		<div class="drawer-content" :class="drawerLayoutClass()">
			<!-- Edit mode for bp_group_document -->
			<div v-if="isEditMode && itemType() === 'bp_group_document'" class="drawer-two-column">
				<div class="drawer-preview-column">
					<img
						v-if="hasImagePreview()"
						:src="imagePreviewUrl()"
						:alt="title()"
						class="drawer-preview-image"
					/>
					<img
						v-else
						:src="noPreviewImageUrl"
						alt="No Preview Available"
						class="drawer-no-preview-image"
					/>
				</div>
				<div class="drawer-details-column">
					<BpGroupDocumentForm
						:itemId="itemId"
						@cancel-edit="cancelEdit"
					/>
				</div>
			</div>

			<!-- Edit mode for external_link -->
			<div v-else-if="isEditMode && itemType() === 'external_link'" class="drawer-single-column">
				<ExternalLinkForm
					:itemId="itemId"
					@cancel-edit="cancelEdit"
				/>
			</div>

			<!-- Edit mode for bp_doc -->
			<div v-else-if="isEditMode && itemType() === 'bp_doc'" class="drawer-single-column drawer-bp-doc-edit">
				<p>To edit this document, please visit the <a :href="editUrl()">document editing page</a>.</p>
				<button class="cac-button cac-button-secondary drawer-cancel-button" @click="cancelEdit">Cancel</button>
			</div>

			<!-- View mode - Two-column layout for forum_attachment and bp_group_document -->
			<div v-else-if="hasTwoColumnLayout()" class="drawer-two-column">
				<div class="drawer-preview-column">
					<img
						v-if="hasImagePreview()"
						:src="imagePreviewUrl()"
						:alt="title()"
						class="drawer-preview-image"
					/>
					<img
						v-else
						:src="noPreviewImageUrl"
						alt="No Preview Available"
						class="drawer-no-preview-image"
					/>
				</div>
				<div class="drawer-details-column">
					<div class="drawer-field">
						<label class="drawer-field-label">File</label>
						<div class="drawer-field-value">
							<a :href="url()">{{ fileName() }}</a>
							<div class="drawer-file-size">{{ fileSize() }}</div>
						</div>
					</div>

					<div class="drawer-field" v-if="description().length > 0">
						<label class="drawer-field-label">Details</label>
						<div class="drawer-field-value">{{ description() }}</div>
					</div>

					<div class="drawer-field">
						<label class="drawer-field-label">Date Uploaded</label>
						<div class="drawer-field-value">{{ date() }}</div>
					</div>

					<div class="drawer-field">
						<label class="drawer-field-label">Added By</label>
						<div class="drawer-field-value">
							<a :href="addedByUrl()">{{ addedByName() }}</a>
						</div>
					</div>

					<div class="drawer-field" v-if="itemFolders().length > 0">
						<label class="drawer-field-label">Tagged</label>
						<div class="drawer-field-value">
							<a
								class="item-folder-link"
								v-for="(folder, index) in itemFolders()"
								:key="folder"
								v-on:click="onFolderClick(folder)"
							>{{ folder }}</a>
						</div>
					</div>

					<div class="drawer-field" v-if="isForumAttachment()">
						<label class="drawer-field-label">Posted In</label>
						<div class="drawer-field-value">
							<a :href="topicUrl()">{{ topicTitle() }}</a>
						</div>
					</div>

					<div class="drawer-actions" v-if="canEdit()">
						<button class="cac-button drawer-edit-button" v-if="canEditInline()" @click="onEditClick">Edit</button>
						<button class="cac-button cac-button-secondary drawer-delete-button" @click="onDeleteClick">Delete</button>
					</div>
				</div>
			</div>

			<!-- Single-column layout for external_link and bp_doc -->
			<div v-else class="drawer-single-column">
				<div class="drawer-field">
					<label class="drawer-field-label">File name</label>
					<div class="drawer-field-value">
						<a :href="url()">{{ title() }}</a>
					</div>
				</div>

				<div class="drawer-field" v-if="description().length > 0">
					<label class="drawer-field-label">Details</label>
					<div class="drawer-field-value">{{ description() }}</div>
				</div>

				<div class="drawer-field">
					<label class="drawer-field-label">Date Uploaded</label>
					<div class="drawer-field-value">{{ date() }}</div>
				</div>

				<div class="drawer-field">
					<label class="drawer-field-label">Added By</label>
					<div class="drawer-field-value">
						<a :href="addedByUrl()">{{ addedByName() }}</a>
					</div>
				</div>

				<div class="drawer-field" v-if="itemFolders().length > 0">
					<label class="drawer-field-label">Tagged</label>
					<div class="drawer-field-value">
						<a
							class="item-folder-link"
							v-for="(folder, index) in itemFolders()"
							:key="folder"
							v-on:click="onFolderClick(folder)"
						>{{ folder }}</a>
					</div>
				</div>

				<div class="drawer-actions" v-if="canEdit()">
					<button class="drawer-edit-button" v-if="canEditInline()" @click="onEditClick">Edit</button>
					<button class="drawer-delete-button" @click="onDeleteClick">Delete</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import AjaxTools from '../mixins/AjaxTools.js'
	import BpGroupDocumentForm from './Forms/BpGroupDocumentForm.vue'
	import ExternalLinkForm from './Forms/ExternalLinkForm.vue'
	import 'vuejs-dialog/dist/vuejs-dialog.min.css';

	export default {
		components: {
			BpGroupDocumentForm,
			ExternalLinkForm
		},

		computed: {
			noPreviewImageUrl() {
				const { imgUrlBase } = window.CACGroupLibrary;
				return imgUrlBase + 'no-preview.svg'
			}
		},

		data() {
			return {
				isEditMode: false
			}
		},

		watch: {
			'$store.state.submitInProgress': function(newVal, oldVal) {
				// When submit finishes (goes from true to false), close edit mode
				if (oldVal === true && newVal === false && this.isEditMode) {
					this.isEditMode = false
				}
			},
			'$store.state.successMessage': function(newVal) {
				// Also close edit mode when success message appears
				if (newVal && newVal.length > 0 && this.isEditMode) {
					this.isEditMode = false
				}
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

			drawerLayoutClass() {
				return this.hasTwoColumnLayout() ? 'drawer-layout-two-column' : 'drawer-layout-single-column'
			},

			editUrl() {
				const item = this.getItem()
				return item.hasOwnProperty( 'edit_url' ) ? item.edit_url : ''
			},

			fileName() {
				return this.title()
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

			hasImagePreview() {
				const fileType = this.fileType()
				const imageTypes = ['jpg', 'jpeg', 'gif', 'bmp', 'png', 'svg', 'tif', 'tiff', 'webp']
				return imageTypes.includes( fileType.toLowerCase() )
			},

			hasTwoColumnLayout() {
				const type = this.itemType()
				return type === 'forum_attachment' || type === 'bp_group_document'
			},

			imagePreviewUrl() {
				return this.url()
			},

			isForumAttachment() {
				return 'forum_attachment' === this.itemType()
			},

			itemFolders() {
				return this.getItem().folders || []
			},

			itemType() {
				return this.getItem().item_type
			},

			cancelEdit() {
				this.isEditMode = false
			},

			onDeleteClick() {
				const app = this

				app.deleteInProgress = true

				const dialogOptions = {
					cancelText: 'Cancel',
					customClass: 'group-library-dialog delete-item-dialog',
					okText: 'Delete',
				}

				app.$dialog
					.confirm( 'Item will be permanently deleted. Are you sure you want to continue?', dialogOptions )
					.then( function( dialog ) {
						app.$store.dispatch(
							'deleteItem',
							app.itemId
						)
						.then( function( response ) {
							return response.json()
						}).then( function( json ) {
							if ( json.success ) {
								app.$store.dispatch( 'refetchItems' )
								.then( function() {
									app.postAjaxFormActions( {
										message: json.message
									} )
								})
							}
						}).catch( function( ex ) {
							console.log( 'failed', ex )
						})
					})
					.catch( function( dialog ) {
						app.deleteInProgress = false
					})
			},

			onEditClick() {
				// For bp_doc and forum_attachment, we need to handle differently
				const item = this.getItem()

				// bp_doc items with edit_url should navigate to external URL
				if ( item.item_type === 'bp_doc' && this.editUrl() ) {
					window.location.href = this.editUrl()
					return
				}

				// forum_attachment items cannot be edited
				if ( item.item_type === 'forum_attachment' ) {
					// For now, do nothing
					return
				}

				// For bp_group_document and external_link, switch to edit mode
				this.fillForm()
				this.isEditMode = true
			},

			canEditInline() {
				const item = this.getItem()
				// Forum attachments cannot be edited inline
				return item.item_type !== 'forum_attachment'
			},

			onFolderClick(folder) {
				let folderQuery = Object.assign( {}, this.$route.query )
				folderQuery.page = 1

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

			fillForm() {
				const item = this.getItem()

				switch ( this.itemType() ) {
					case 'bp_group_document' :
						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'bpGroupDocument',
								field: 'itemId',
								value: item.id
							}
						)

						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'bpGroupDocument',
								field: 'title',
								value: item.title
							}
						)

						const description = item.hasOwnProperty( 'description' ) ? item.description : ''
						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'bpGroupDocument',
								field: 'description',
								value: description
							}
						)

						if ( item.hasOwnProperty( 'folders' ) && item.folders.length > 0 ) {
							this.$store.commit(
								'setFormFieldValue',
								{
									form: 'bpGroupDocument',
									field: 'folder',
									value: item.folders
								}
							)
						}
					break;

					case 'external_link' :
						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'externalLink',
								field: 'itemId',
								value: item.id
							}
						)

						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'externalLink',
								field: 'title',
								value: item.title
							}
						)

						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'externalLink',
								field: 'url',
								value: item.url
							}
						)

						const linkDescription = item.hasOwnProperty( 'description' ) ? item.description : ''
						this.$store.commit(
							'setFormFieldValue',
							{
								form: 'externalLink',
								field: 'description',
								value: linkDescription
							}
						)

						if ( item.hasOwnProperty( 'folders' ) && item.folders.length > 0 ) {
							this.$store.commit(
								'setFormFieldValue',
								{
									form: 'externalLink',
									field: 'folder',
									value: item.folders
								}
							)
						}
					break;
				}
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

		mixins: [
			AjaxTools,
		],

		props: [
			'itemId'
		]
	}
</script>

<style>
.item-details-drawer {
	border-bottom: 1px solid var(--med-grey);
	--line-x: -24px;      /* x position of the vertical stroke */
	--line-w: 23px;      /* total width (includes the horizontal segment) */
	--radius: 14px;      /* big rounded corner */
	--thickness: 2px;
	--stroke: #d6d9df;
	--top-gap: 0px;      /* set if the line shouldn't start at the very top */
}

.drawer-content {
	margin-left: calc(40px + 24px + 12px);
	padding: 24px 48px 24px 24px;
	max-width: 100%;
	background: #F3F3F3;
	position: relative;
}

.drawer-content::before {
  content: "";
  position: absolute;
  left: var(--line-x);
  top: var(--top-gap);
  bottom: 0;
  height: 50%;
  width: var(--line-w);

  border-left: var(--thickness) solid var(--stroke);
  border-bottom: var(--thickness) solid var(--stroke);

  /* This creates the large-radius 90° turn */
  border-bottom-left-radius: var(--radius);

  pointer-events: none;
}

.drawer-two-column {
	display: grid;
	grid-template-columns: 320px 1fr;
	gap: 48px;
}

.drawer-preview-column {
	display: flex;
	align-items: flex-start;
	justify-content: center;
}

.drawer-preview-image {
	max-width: 100%;
	max-height: 400px;
	object-fit: contain;
}

.drawer-no-preview-image {
	width: 100%;
	max-width: 320px;
	height: auto;
}

.drawer-details-column {
	display: flex;
	flex-direction: column;
	gap: 16px;
}

.drawer-single-column {
	display: flex;
	flex-direction: column;
	gap: 16px;
	max-width: 800px;
}

.drawer-field {
	display: grid;
	grid-template-columns: 140px 1fr;
	gap: 16px;
}

.drawer-field-label {
	font-weight: 600;
	color: #555;
	text-align: left;
}

.drawer-field-value {
	color: #555;
}

.drawer-field-value a {
	color: var(--link-color, #1C576C);
	text-decoration: underline;
}

.drawer-field-value a:hover {
	text-decoration: none;
}

.drawer-file-size {
	color: var(--dark-grey);
	font-size: 14px;
	margin-top: 4px;
}

.drawer-actions {
	display: flex;
	gap: 12px;
	margin-top: 16px;
}

/* Form styling when in drawer context */
.drawer-details-column .add-new-form,
.drawer-single-column .add-new-form {
	background: transparent;
	padding: 0;
}

.drawer-details-column .add-new-field,
.drawer-single-column .add-new-field {
	display: grid;
	grid-template-columns: 140px 1fr;
	gap: 16px;
	align-items: start;
	margin-bottom: 16px;
}

.drawer-details-column .add-new-field label,
.drawer-single-column .add-new-field label {
	font-weight: 600;
	color: #555;
	text-align: left;
	padding-top: 8px;
}

.drawer-details-column .add-new-submit,
.drawer-single-column .add-new-submit {
	display: flex;
	gap: 12px;
	margin-top: 24px;
	grid-column: 1 / -1;
}

.drawer-details-column .add-edit-silent-toggle,
.drawer-single-column .add-edit-silent-toggle {
	grid-column: 1 / -1;
}

.drawer-bp-doc-edit {
	padding: 0;
}

.drawer-bp-doc-edit p {
	margin-bottom: 16px;
}

@media screen and (max-width: 768px) {
	.item-details-drawer {
		padding: 24px;
	}

	.drawer-content {
		margin-left: 0;
		padding: 16px;
	}

	.drawer-content::before {
		display: none;
	}

	.drawer-two-column {
		grid-template-columns: 1fr;
		gap: 24px;
	}

	.drawer-field {
		grid-template-columns: 1fr;
		gap: 8px;
	}

	.drawer-details-column .add-new-field,
	.drawer-single-column .add-new-field {
		grid-template-columns: 1fr;
		gap: 8px;
	}

	.drawer-details-column .add-new-field label,
	.drawer-single-column .add-new-field label {
		padding-top: 0;
	}
}
</style>
