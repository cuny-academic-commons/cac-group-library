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

			<div class="add-new-field">
				<label for="file-uploader">
					Select your file
					<button
						class="tooltip-button"
						v-if="fileTooltip"
						v-tooltip.right-start="fileTooltip"
					/>
				</label>

				<FileUploader
					formName="bpGroupDocument"
					fieldName="file"
					:itemId="getItemId()"
					:required="!isEditMode"
				/>
			</div>

			<div class="add-new-field add-new-field-dropdown">
				<label
					for="add-new-file-folder"
				>Tag (optional)</label>

				<FolderTagSelector
					form="bpGroupDocument"
					inputId="add-new-file-folder"
				/>
			</div>

			<div class="add-edit-silent-toggle">
				<SilentToggle
					:defaultValue="isSilentChecked()"
					:label="silentToggleLabel"
				/>
			</div>

			<div class="add-new-submit">
				<SubmitButton
					:buttonText="submitButtonText"
					formName="bpGroupDocument"
				/>

				<DeleteButton
					:itemId="getItemId()"
					v-if="isEditMode"
				/>
				
				<button
					v-if="showCancelButton"
					type="button"
					class="drawer-cancel-button"
					@click="onCancelClick"
				>Cancel</button>
			</div>
		</form>
	</div>
</template>

<script>
	import DeleteButton from '../DeleteButton.vue'
	import FileUploader from '../FileUploader.vue'
	import FormField from '../FormField.vue'
	import FolderTagSelector from '../FolderTagSelector.vue'
	import FormValidation from '../../mixins/FormValidation'
	import SilentToggle from '../SilentToggle.vue'
	import SubmitButton from '../SubmitButton.vue'

	export default {
		components: {
			DeleteButton,
			FileUploader,
			FormField,
			FormValidation,
			FolderTagSelector,
			SilentToggle,
			SubmitButton,
		},

		computed: {
			fileTooltip() {
				const { maxUploadSizeFormatted, uploadFiletypes } = window.CACGroupLibrary
				const types = uploadFiletypes.join( ' ')

				return 'Max file size: ' + maxUploadSizeFormatted + '. Supported file types: ' + types
			},

			isEditMode() {
				return this.getItemId() > 0
			},

			isFormValidated() {
				return this.isFormValid( this.formName )
			},

			silentToggleLabel() {
				return this.isEditMode ? 'Silent edit' : 'Silent upload'
			},

			submitButtonText() {
				return this.isEditMode ? 'Save Changes' : 'Upload'
			},

			showCancelButton() {
				// Show cancel button when used in drawer (itemId passed as prop)
				return this.$props.itemId !== null && this.$props.itemId !== undefined
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

		methods: {
			isSilentChecked() {
				return this.isEditMode
			},

			onCancelClick() {
				this.$emit('cancel-edit')
			},

			getItemId() {
				// Check if itemId is passed as a prop (drawer context)
				if ( this.$props.itemId !== null && this.$props.itemId !== undefined ) {
					return this.$props.itemId
				}
				// Otherwise get from route (standalone edit view)
				const { params } = this.$route
				return params.hasOwnProperty( 'itemId' ) ? Number( params.itemId ) : 0
			}
		},

		props: {
			itemId: {
				type: Number,
				required: false,
				default: null
			}
		}
	}
</script>

<style>
.add-new-form {
	padding-bottom: 100px;
}

.static-file-label {
	font-weight: bold;
}
</style>
