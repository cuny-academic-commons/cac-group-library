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
					v-on:click="onEditClick()"
				>Delete</button>
			</div>
		</div>

	</div>
</template>

<script>
	export default {
		computed: {
			fieldId() {
				return 'edit-folder-' + this.folderName
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
			onCancelClick() {
				this.editValue = this.savedValue
				this.isEditMode = false
			},

			onEditClick() {
				this.isEditMode = true
			},

			onUpdateClick() {
				const app = this

				this.$store.commit( 'setSubmitInProgress', { value: true } )

				this.$store.dispatch(
					'updateFolderName',
					{
						value: this.editValue
					}
				).then( function( response ) {
					return response.json()
				} ).then( function( json ) {
					if ( json.success ) {
						app.$store.commit(
							'setSubmitInProgress',
							{ value: false }
						)

						app.isEditMode = false
						app.savedValue = app.editValue
					}
				} ).catch( function( ex ) {
					console.log( 'failed', ex )
				} )
			},
		},

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

	.edit-folder-non-edit-mode .edit-folder-name {
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

	.edit-folder-actions button.edit-folder-rename {
		border-right: 1px solid #1c576c;
		color: #1c576c;
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
