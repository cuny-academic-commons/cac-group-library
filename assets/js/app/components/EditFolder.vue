<template>
	<div>
		<div v-if="isEditMode" class="edit-folder-edit-mode">
			<input
				:id="fieldId"
				:name="fieldId"
				v-model="editValue"
			/>

			<div class="edit-folder-actions">
				<button
					:class="{ 'submit-in-progress': submitInProgress }"
					v-bind:style="backgroundStyles( 'submit' )"
					v-on:click="onUpdateClick()"
				>Update</button>

				<button
					class="edit-folder-cancel"
					v-on:click="onCancelClick()"
				>Cancel</button>
			</div>
		</div>

		<div v-else class="edit-folder-non-edit-mode">
			<div class="edit-folder-name">
				<div class="folder-icon">
					<img
						:src="folderIconUrl"
					/>
				</div>

				<router-link
					class="go-to-folder"
					:to="goToFolderLink"
				>{{ savedValue }}</router-link>
			</div>

			<div class="edit-folder-actions">
				<button
					class="edit-folder-rename"
					v-on:click="onEditClick()"
				>Rename</button><button
					class="edit-folder-delete"
					v-on:click="onDeleteClick()"
				>Delete</button>
			</div>
		</div>

	</div>
</template>

<script>
	import Vue from 'vue'
	import AjaxTools from '../mixins/AjaxTools.js'
	import DeleteFolderDialog from './DeleteFolderDialog.vue'
	import 'vuejs-dialog/dist/vuejs-dialog.min.css';

	export default {
		components: {
			DeleteFolderDialog,
		},

		computed: {
			fieldId() {
				return 'edit-folder-' + this.folderName
			},

			folderIconUrl() {
				const { imgUrlBase } = window.CACGroupLibrary;
				return imgUrlBase + 'folder-icon.png'
			},

			editValue: {
				get() {
					const folder = this.$store.state.forms.folderNames[ this.folderName ]
					return folder.editValue
				},

				set( value ) {
					this.$store.commit(
						'setFolderNameFormValue',
						{
							folderName: this.folderName,
							field: 'editValue',
							value
						}
					)
				}
			},

			goToFolderLink() {
				return {
					path: '/',
					query: { 'folder': this.folderName }
				}
			},

			isEditMode: {
				get() {
					return this.folderName === this.$store.state.currentlyEditedFolder
				},

				set( value ) {
					const newValue = value ? this.folderName : ''
					this.$store.commit( 'setCurrentlyEditedFolder', { value: newValue } )
				}
			},

			savedValue: {
				get() {
					const folder = this.$store.state.forms.folderNames[ this.folderName ]

					// This may happen immediately after creation.
					if ( 'undefined' === typeof( folder ) ) {
						return this.folderName
					}

					return folder.savedValue
				},

				set( value ) {
					this.$store.commit(
						'setFolderNameFormValue',
						{
							folderName: this.folderName,
							field: 'savedValue',
							value
						}
					)
				}
			},
		},

		methods: {
			checkForEditing() {
				const app = this

				const { currentlyEditedFolder } = this.$store.state
				if ( ! currentlyEditedFolder ) {
					return false
				}

				const dialogOptions = {
					cancelText: 'Cancel',
					customClass: 'group-library-dialog discard-changes-dialog',
					okText: 'Discard Changes',
				}

				return app.$dialog
					.confirm( 'You are currently editing a different folder\'s name. Do you want to discard your changes?', dialogOptions )
					.then( function( dialog ) {
						app.isEditMode = false
						app.editValue = app.savedValue
					})
					.catch( function( dialog ) {
						return true
					})
			},

			onCancelClick() {
				this.editValue = this.savedValue
				this.isEditMode = false
			},

			onDeleteClick() {
				if ( this.checkForEditing() ) {
					return
				}

				const app = this

				this.$dialog.registerComponent( 'deleteFolderDialog', DeleteFolderDialog )

				const dialogMessage = {
					title: this.folderName,
					body: 'Are you sure you want to DELETE the folder "' + this.folderName + '"?'
				}

				this.$dialog.confirm(
					dialogMessage,
					{
						customClass: 'group-library-dialog-container-wide',
						view: 'deleteFolderDialog',
						html: true,
					}
				).then( function( dialog ) {
					app.deleteInProgress = true

					app.$store.dispatch(
						'deleteFolder',
						{
							deleteType: dialog.data,
							folderName: app.folderName
						}
					).then( function( response ) {
						return response.json()
					} ).then( function( json ) {
						if ( json.success ) {
							// Refetch all items in the background, since item have changed.
							app.$store.dispatch( 'refetchItems' )

							app.$store.dispatch(
								'fetchFoldersOfGroup'
							).then( function() {
								app.$store.commit( 'setUpFolderNamesForm' )
								app.deleteInProgress = false
							} )
						}
					} )
				} )
			},

			onEditClick() {
				if ( this.checkForEditing() ) {
					return
				}

				this.isEditMode = true
			},

			onUpdateClick() {
				const app = this

				this.submitInProgress = true

				this.$store.dispatch(
					'updateFolderName',
					{
						value: this.editValue
					}
				).then( function( response ) {
					return response.json()
				} ).then( function( json ) {
					if ( json.success ) {
						// Refetch all items in the background, since item have changed.
						app.$store.dispatch( 'refetchItems' )

						app.$store.dispatch( 'fetchFoldersOfGroup' )
						.then( function() {
							app.$store.commit( 'setUpFolderNamesForm' )
							app.submitInProgress = false
							app.isEditMode = false
							app.savedValue = app.editValue
						} )
					}
				} ).catch( function( ex ) {
					console.log( 'failed', ex )
				} )
			},
		},

		mixins: [
			AjaxTools
		],

		props: {
			folderName: String
		}
	}
</script>

<style>
	.edit-folder-edit-mode,
	.edit-folder-non-edit-mode {
		display: flex;
	}

	.edit-folder-non-edit-mode .folder-icon {
		height: 21px;
		margin-right: 12px;
	}

	.edit-folder-non-edit-mode .folder-icon img {
		height: 14px;
		vertical-align: middle;
	}

	.edit-folder-non-edit-mode .edit-folder-name {
		display: flex;
		align-items: center;
		flex-basis: 70%;
		font-weight: 700;
	}

	.edit-folder-non-edit-mode .edit-folder-actions {
		flex-basis: 30%;
		text-align: right;
	}

	.edit-folder-non-edit-mode .edit-folder-actions button,
	.edit-folder-non-edit-mode .edit-folder-actions button:hover,
	.edit-folder-actions button.edit-folder-cancel,
	.edit-folder-actions button.edit-folder-cancel:hover {
		background: none;
		border: 0;
		border-radius: 0;
		padding-bottom: 0;
		padding-top: 0;
	}

	.edit-folder-non-edit-mode .edit-folder-actions button:hover,
	.edit-folder-actions button.edit-folder-cancel:hover {
		text-decoration: underline;
	}

	.edit-folder-actions button.edit-folder-rename {
		border-right: 1px solid #1c576c;
		color: #1c576c;
	}

	.edit-folder-actions button.edit-folder-rename:hover {
		border-right: 1px solid #1c576c;
	}

	.edit-folder-actions button.edit-folder-delete {
		color: #f00;
	}

	.edit-folder-edit-mode input {
		border: 1px solid #ccc;
		border-radius: 4px;
		font-size: 12px;
		line-height: 25px;
		margin-right: 10px;
		padding: 0 8px;
		width: 200px;
	}
</style>
