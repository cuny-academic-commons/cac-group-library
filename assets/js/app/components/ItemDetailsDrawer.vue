<template>
	<div class="item-details-drawer">
		<div class="drawer-content" :class="drawerLayoutClass()">
			<!-- Two-column layout for forum_attachment and bp_group_document -->
			<div v-if="hasTwoColumnLayout()" class="drawer-two-column">
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
							>{{ folder }}<span v-if="index < itemFolders().length - 1">, </span></a>
						</div>
					</div>
					
					<div class="drawer-field" v-if="isForumAttachment()">
						<label class="drawer-field-label">Posted In</label>
						<div class="drawer-field-value">
							<a :href="topicUrl()">{{ topicTitle() }}</a>
						</div>
					</div>
					
					<div class="drawer-actions" v-if="canEdit()">
						<button class="drawer-edit-button" @click="onEditClick">Edit</button>
						<button class="drawer-delete-button" @click="onDeleteClick">Delete</button>
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
					<label class="drawer-field-label">Date uploaded</label>
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
						>{{ folder }}<span v-if="index < itemFolders().length - 1">, </span></a>
					</div>
				</div>
				
				<div class="drawer-actions" v-if="canEdit()">
					<button class="drawer-edit-button" @click="onEditClick">Edit</button>
					<button class="drawer-delete-button" @click="onDeleteClick">Delete</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import AjaxTools from '../mixins/AjaxTools.js'
	import 'vuejs-dialog/dist/vuejs-dialog.min.css';

	export default {
		computed: {
			noPreviewImageUrl() {
				const { imgUrlBase } = window.CACGroupLibrary;
				return imgUrlBase + 'no-preview.svg'
			}
		},

		data() {
			return {
				deleteInProgress: false
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
				const imageTypes = ['jpg', 'jpeg', 'gif', 'bmp', 'png', 'svg', 'tif']
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
				// Emit event to parent to handle edit mode
				this.$emit('edit-item', this.itemId)
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
	background: #F3F3F3;
	padding: 24px 48px;
	border-bottom: 1px solid var(--med-grey);
}

.drawer-content {
	max-width: 100%;
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

.drawer-edit-button {
	background: #000;
	border: none;
	color: #fff;
	font-size: 16px;
	padding: 9px 24px;
	cursor: pointer;
}

.drawer-edit-button:hover {
	background: #333;
}

.drawer-delete-button {
	background: #fff;
	border: 1px solid #000;
	color: #000;
	font-size: 16px;
	padding: 9px 24px;
	cursor: pointer;
}

.drawer-delete-button:hover {
	background: #f5f5f5;
}

@media screen and (max-width: 768px) {
	.item-details-drawer {
		padding: 24px;
	}

	.drawer-two-column {
		grid-template-columns: 1fr;
		gap: 24px;
	}

	.drawer-field {
		grid-template-columns: 1fr;
		gap: 8px;
	}
}
</style>
