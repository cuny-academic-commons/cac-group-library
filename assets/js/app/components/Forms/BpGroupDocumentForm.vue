<template>
	<div class="add-new-form">
		<form
			:class="'add-new-form-' + formName"
			enctype="multipart/form-data"
			v-on:submit.prevent="isFormValidated"
		>
			<FormField
				fieldLabel="Title (required)"
				fieldId="add-new-file-title"
				:required="true"
				formName="bpGroupDocument"
				fieldName="title"
				fieldType="text"
			/>

			<FormField
				fieldLabel="Description"
				fieldId="add-new-file-description"
				:required="false"
				formName="bpGroupDocument"
				fieldName="description"
				fieldType="textarea"
				:maxlength="350"
			/>

			<FormField
				buttonText="Select file"
				:disabled="isEditMode"
				fieldLabel="Select your file"
				fieldId="add-new-file-file"
				:required="true"
				formName="bpGroupDocument"
				fieldName="file"
				fieldType="file"
				:tooltip="fileTooltip"
			/>

			<div class="add-new-field add-new-field-dropdown">
				<label
					for="add-new-file-folder"
				>Add to folder (optional)</label>

				<FolderSelector
					form="bpGroupDocument"
					inputId="add-new-file-folder"
				/>
			</div>

			<div class="add-new-submit">
				<SubmitButton
					:buttonText="submitButtonText"
					formName="bpGroupDocument"
				/>

				<DeleteButton 
					:itemId="itemId"
					v-if="isEditMode"
				/>
			</div>
		</form>
	</div>
</template>

<script>
	import DeleteButton from '../DeleteButton.vue'
	import FormField from '../FormField.vue'
	import FolderSelector from '../FolderSelector.vue'
	import FormValidation from '../../mixins/FormValidation'
	import SubmitButton from '../SubmitButton.vue'

	export default {
		components: {
			DeleteButton,
			FormField,
			FormValidation,
			FolderSelector,
			SubmitButton,
		},

		computed: {
			fileTooltip() {
				const { maxUploadSizeFormatted, uploadFiletypes } = window.CACGroupLibrary
				const types = uploadFiletypes.join( ' ')

				return 'Max file size: ' + maxUploadSizeFormatted + '. Supported file types: ' + types
			},

			isEditMode() {
				return this.itemId > 0
			},

			isFormValidated() {
				return this.isFormValid( this.formName )
			},

			submitButtonText() {
				return this.isEditMode ? 'Save Changes' : 'Upload'
			},

			itemId: {
				get() {
					return this.$store.state.forms[ this.formName ].itemId
				}
			},

			title: {
				get() {
					return this.$store.state.forms[ this.formName ].url
				},
				set( value ) {
					this.$store.commit(
						'setFormFieldValue',
						{
							form: this.formName,
							field: 'title',
							value
						}
					)
				}
			},

			url: {
				get() {
					return this.$store.state.forms[ this.formName ].url
				},
				set( value ) {
					this.$store.commit(
						'setFormFieldValue',
						{
							form: this.formName,
							field: 'url',
							value
						}
					)
				}
			},
		},

		data() {
			return {
				formName: 'bpGroupDocument'
			}
		},

		params: {
			itemId: Number
		}
	}
</script>

<style>
.add-new-form {
	padding-bottom: 100px;
}
</style>
