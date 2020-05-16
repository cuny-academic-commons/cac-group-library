<template>
	<div class="add-new-form">
		<p>Please note: If you are linking to a document or other file that&apos;s hosted externally, it will have a time stamp from when it was added to the group library. These files are linked, which means any changes made in the original location won&apos;t trigger notifications to the group.</p>

		<form class="add-new-form-externalLink" v-on:submit.prevent="isFormValidated">
			<FormField
				fieldLabel="Title (required)"
				fieldId="add-new-external-link-title"
				:required="true"
				formName="externalLink"
				fieldName="title"
				fieldType="text"
			/>

			<FormField
				description="E.g. https://docs.google.com/document/... Review your sharing options for the source file, so it is accessible to group members."
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
				<SubmitButton
					:buttonText="submitButtonText"
					formName="externalLink"
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
	import FormValidation from '../../mixins/FormValidation'
	import FolderSelector from '../FolderSelector.vue'
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
			isFormValidated() {
				return this.isFormValid( 'externalLink' )
			},

			isEditMode() {
				return this.itemId > 0
			},

			itemId() {
				return this.$store.state.forms.externalLink.itemId
			},

			submitButtonText() {
				return this.itemId > 0 ? 'Update link' : 'Add link'
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
