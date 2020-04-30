<template>
	<div :class="className">
		<label
			:for="fieldId"
		>{{fieldLabel}}</label>

		<input
			v-if="isInputTypeText"
			:id="fieldId"
			:name="fieldId"
			:required="required"
			:type="fieldValidationType"
			v-on:blur="setFieldVisited()"
			v-on:keyup="doValidateForm()"
			v-model="value"
		/>

		<input
			v-if="isInputTypeFile"
			:id="fieldId"
			:name="fieldId"
			:required="required"
			type="file"
			v-on:blur="setFieldVisited()"
			v-on:change="setFile"
		/>

		<textarea
			v-if="isInputTypeTextarea"
			:id="fieldId"
			:name="fieldId"
			:required="required"
			v-on:blur="setFieldVisited()"
			v-on:keyup="doValidateForm()"
			v-model="value"
		/>

		<p
			class="description"
			v-if="description"
		>{{ description }}</p>

		<p
			class="field-error"
			v-if="validationError"
		>{{ validationError }}</p>
	</div>
</template>

<script>
	import FormValidation from '../mixins/FormValidation.js'

	export default {
		computed: {
			className() {
				const { fieldType, validationError } = this

				let classes = [
					'add-new-field',
					'add-new-field-' + fieldType
				]

				if ( validationError ) {
					classes.push( 'has-error' )
				}

				return classes.join( ' ' )
			},

			isInputTypeFile() {
				return 'file' === this.fieldType
			},

			isInputTypeText() {
				return 'text' === this.fieldType
			},

			isInputTypeTextarea() {
				return 'textarea' === this.fieldType
			},

			validationError() {
				const error = this.getFieldError( this.fieldId )

				if ( error.length > 0 ) {
					return error
				}

				return ''
			},

			value: {
				get() {
					return this.$store.state.forms[ this.formName ][ this.fieldName ]
				},

				set( value ) {
					this.$store.commit(
						'setFormFieldValue',
						{
							form: this.formName,
							field: this.fieldName,
							value
						}
					)
				}
			}
		},

		mixins: [
			FormValidation
		],

		methods: {
			// Too lazy to refactor for targeted revalidation.
			doValidateForm() {
				this.validateForm( this.formName )
			},

			setFieldVisited() {
				this.$store.commit(
					'setFieldHasBeenVisited',
					{
						fieldName: this.fieldId,
						formName: this.formName,
					}
				)
			},

			setFile(e) {
				this.$store.commit(
					'setFormFieldValue',
					{
						form: this.formName,
						field: this.fieldName,
						value: e.target.files[0]
					}
				)
			}
		},

		props: {
			buttonText: String,
			description: String,
			fieldId: String,
			fieldLabel: String,
			fieldName: String,
			fieldType: String,
			fieldValidationType: String,
			formName: String,
			required: Boolean,
			maxlength: Number,
			tooltip: String,
		}
	}
</script>

<style>
	.add-new-field {
		margin-bottom: 20px;
	}

	.add-new-field label {
		display: block;
		font-weight: bold;
	}

	.add-new-folder-container input,
	.add-new-field-text input {
		border: 1px solid #ccc;
		border-radius: 4px;
		font-size: 12px;
		line-height: 35px;
		padding: 0 8px;
		width: calc(100% - 16px);
	}

	.add-new-field p.description {
		font-style: italic;
	}

	.add-new-field-textarea textarea {
		border: 1px solid #ccc;
		border-radius: 4px;
		font-size: 12px;
		height: 76px;
		padding: 8px;
		width: calc(100% - 16px);
	}

	.add-new-folder-container.has-error input,
	.add-new-field.has-error input {
		border-color: #f00;
	}

	.add-new-folder-container.has-error .field-error,
	.add-new-field.has-error .field-error {
		border-radius: 2px;
		color: #f00;
		padding: 5px 0;
		margin-top: 7px;
		width: 80%;
	}
</style>
