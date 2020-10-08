<template>
	<div class="add-new-folder">
		<h3>Add a new folder</h3>

		<div class="add-new-folder-fields edit-folder-edit-mode">
			<input
				id="add-new-folder-editValue"
				name="add-new-folder-editValue"
				v-model="editValue"
			/>

			<div class="edit-folder-actions">
				<button
					:class="{ 'submit-in-progress': submitInProgress }"
					v-bind:style="backgroundStyles( 'submit' )"
					v-on:click="onAddClick()"
				>Add Folder</button>

				<button
					class="edit-folder-cancel"
					v-on:click="onCancelClick()"
				>Cancel</button>
			</div>
		</div>
	</div>
</template>

<script>
	import AjaxTools from '../mixins/AjaxTools.js'

	export default {
		computed: {
			editValue: {
				get() {
					const folder = this.$store.state.forms.folderNames._new
					return folder.editValue
				},

				set( value ) {
					this.$store.commit(
						'setFolderNameFormValue',
						{
							folderName: '_new',
							field: 'editValue',
							value
						}
					)
				}
			},

			isEditMode: {
				get() {
					return '_new' === this.$store.state.currentlyEditedFolder
				},

				set( value ) {
					const newValue = value ? '_new' : ''
					this.$store.commit( 'setCurrentlyEditedFolder', { value: newValue } )
				}
			},

			savedValue: {
				get() {
					const folder = this.$store.state.forms.folderNames._new
					return folder.savedValue
				},

				set( value ) {
					this.$store.commit(
						'setFolderNameFormValue',
						{
							folderName: '_new',
							field: 'savedValue',
							value
						}
					)
				}
			},
		},

		mixins: [
			AjaxTools
		],

		methods: {
			onAddClick() {
				const app = this

				this.submitInProgress = true

				this.$store.dispatch(
					'addFolder',
					{
						value: this.editValue
					}
				).then( function( response ) {
					return response.json()
				} ).then( function( json ) {
					if ( json.success ) {

						app.$store.dispatch( 'fetchFoldersOfGroup' )
						.then( function() {
							app.$store.commit( 'setUpFolderNamesForm' )
							app.submitInProgress = false
							app.isEditMode = false
							app.savedValue = app.editValue

							// Refetch all items in the background, since folders have changed.
							app.$store.dispatch( 'refetchItems' )
						} )
					}
				} ).catch( function( ex ) {
					console.log( 'failed', ex )
				} )
			},

			onCancelClick() {
				this.editValue = ''
				this.isEditMode = false
			},
		}
	}
</script>

<style>
	.add-new-folder {
		background: #f5f5f5;
		border-bottom: 1px solid #ddd;
		border-top: 1px solid #ddd;
		padding: 10px 20px 20px;
	}

	div#content .add-new-folder h3 {
		font-size: 16px;
		margin-bottom: 8px;
	}
</style>
