<template>
	<div class="add-new-form">
		<form class="add-new-form-bpDoc" v-on:submit.prevent="isFormValidated">
			<AddNewField
				fieldLabel="Title (required)"
				:fieldId="'add-new-bp-doc-title'"
				:required="true"
				:formName="formName"
				fieldName="title"
				fieldType="text"
			/>

			<div class="add-new-field">
				<editor
					apiKey="no-api-key"
					id="add-new-bp-doc-content"
					:init="mceInit"
					name="add-new-bp-doc-content"
					v-model="content"
				/>
			</div>

			<div class="add-new-field add-new-field-dropdown">
				<label
					:for="folderInputName"
				>Add to folder (optional)</label>

				<FolderSelector
					:form="formName"
					:inputId="folderInputName"
				/>
			</div>

			<div class="add-new-field add-new-field-dropdown">
				<label
					for="add-new-bp-doc-parent"
				>Select a parent for this Doc</label>

				<p>(Optional) Assigning a parent Doc means that a link to the parent will appear at the bottom of this Doc, and a link to the Doc will appear at the bottom of the parent.</p>

				<v-select
					id="add-new-bp-doc-parent"
					v-model="parent"
					placeholder="(no parent)"
					:options="parentOptions" />

			</div>

			<div class="add-new-submit">
				<AddNewSubmitButton
					buttonText="Add link"
					:formName="formName"
				/>
			</div>
		</form>
	</div>
</template>

<script>
	import Editor from '@tinymce/tinymce-vue'

	import vSelect from 'vue-select'
	import 'vue-select/dist/vue-select.css';

	import AddNewField from '../AddNewField.vue'
	import AddNewSubmitButton from '../AddNewSubmitButton.vue'
	import FolderSelector from '../FolderSelector.vue'
	import FormValidation from '../../mixins/FormValidation'

	export default {
		components: {
			AddNewField,
			AddNewSubmitButton,
			Editor,
			FolderSelector,
			FormValidation,
			vSelect
		},

		computed: {
			content: {
				get() {
					return this.$store.state.forms[ this.formName ].content
				},
				set( value ) {
					this.$store.commit(
						'setFormFieldValue',
						{
							form: this.formName,
							field: 'content',
							value
						}
					)
				}
			},

			folderInputName() {
				return 'add-new-' + this.formNameHyphens + '-folder'
			},

			isFormValidated() {
				return this.validateForm( this.formName )
			},

			mceInit() {
				return {
					theme: 'modern',
					skin: 'lightgray',
					menubar: false,
					plugins: [
					   'lists link image charmap',
					   'fullscreen image wordpress',
					   'media paste'
					 ],
					 toolbar:
					   'undo redo | formatselect | bold italic backcolor | \
						   alignleft aligncenter alignright alignjustify | \
						   bullist numlist outdent indent | removeformat | table',
				}
			},

			parent: {
				get() {
					return this.$store.state.forms[ this.formName ].parent
				},
				set( value ) {
					this.$store.commit(
						'setFormFieldValue',
						{
							form: this.formName,
							field: 'parent',
							value
						}
					)
				}
			},

			parentOptions() {
				return this.$store.state.potentialParentDocs
			},

			title: {
				get() {
					return this.$store.state.forms[ this.formName ].title
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
		},

		created() {
			this.$store.dispatch( 'fetchPotentialParentDocs' )
		},

		data() {
			return {
				formName: 'bpDoc',
				formNameHyphens: 'bp-doc'
			}
		}
	}
</script>

<style>
.add-new-form {
	padding-bottom: 100px;
}

/* Overriding BP styles */
.mce-tinymce button:hover, .mce-tinymce a.button:hover, .mce-tinymce a.button:focus, .mce-tinymce input[type=submit]:hover, .mce-tinymce input[type=button]:hover, .mce-tinymce input[type=reset]:hover {
	border: none;
}
</style>
