<template>
	<div class="add-new-form">
		<p>Please note: If you are linking to a document or other file that&apos;s hosted externally, it will have a time stamp from when it was added to the group library. These files are linked, which means any changes made in the original location won&apos;t trigger notifications to the group.</p>

		<form class="add-new-form-externalLink" v-on:submit.prevent="isFormValidated">
			<AddNewField
				fieldLabel="Title (required)"
				fieldId="add-new-external-link-title"
				:required="true"
				formName="externalLink"
				fieldName="title"
				fieldType="text"
			/>

			<AddNewField
				description="E.g. https://docs.google.com/document/&hellip; Review your sharing options for the source file, so it is accessible to group members."
				fieldLabel="Link (required)"
				fieldId="add-new-external-link-url"
				:required="true"
				formName="externalLink"
				fieldName="url"
				fieldType="text"
				fieldValidationType="url"
			/>

			<div class="add-new-field add-new-field-dropdown">
				<label
					for="add-new-external-link-folder"
				>Add to folder (optional)</label>

				<FolderSelector
					form="externalLink"
					inputId="add-new-external-link-folder"
				/>
			</div>

			<div class="add-new-submit">
				<AddNewSubmitButton
					buttonText="Add link"
					formName="externalLink"
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
			isFormValidated() {
				return this.validateForm( 'externalLink' )
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
