<template>
	<div class="add-new-form">
		<form
			class="add-new-form-externalLink"
			enctype="multipart/form-data"
			v-on:submit.prevent="isFormValidated"
		>
			<AddNewField
				fieldLabel="Title (required)"
				fieldId="add-new-file-title"
				:required="true"
				formName="bpGroupDocument"
				fieldName="title"
				fieldType="text"
			/>

			<AddNewField
				fieldLabel="Description"
				fieldId="add-new-file-description"
				:required="false"
				formName="bpGroupDocument"
				fieldName="description"
				fieldType="textarea"
				:maxlength="350"
			/>

			<AddNewField
				buttonText="Select file"
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
				<AddNewSubmitButton
					buttonText="Upload"
					formName="bpGroupDocument"
				/>
			</div>
		</form>
	</div>
</template>

<script>
	import AddNewField from '../AddNewField.vue'
	import AddNewSubmitButton from '../AddNewSubmitButton.vue'
	import FolderSelector from '../FolderSelector.vue'
	import FormValidation from '../../mixins/FormValidation'

	export default {
		components: {
			AddNewField,
			AddNewSubmitButton,
			FolderSelector,
			FormValidation
		},

		computed: {
			fileTooltip() {
				const { maxUploadSizeFormatted, uploadFiletypes } = window.CACGroupLibrary
				const types = uploadFiletypes.join( ' ')

				return 'Max file size: ' + maxUploadSizeFormatted + '. Supported file types: ' + types
			},

			isFormValidated() {
				return this.isFormValid( 'externalLink' )
			},

			title: {
				get() {
					return this.$store.state.forms.externalLink.title
				},
				set( value ) {
					this.$store.commit(
						'setFormFieldValue',
						{
							form: 'externalLink',
							field: 'title',
							value
						}
					)
				}
			},

			url: {
				get() {
					return this.$store.state.forms.externalLink.url
				},
				set( value ) {
					this.$store.commit(
						'setFormFieldValue',
						{
							form: 'externalLink',
							field: 'url',
							value
						}
					)
				}
			},
		},
	}
</script>

<style>
.add-new-form {
	padding-bottom: 100px;
}
</style>
