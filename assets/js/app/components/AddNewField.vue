<template>
	<div :class="className">
		<label :for="fieldId">
			{{fieldLabel}}
			<button
				class="tooltip-button"
				v-if="hasTooltip"
				v-tooltip.right-start="tooltipText"
			/>
		</label>

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

			hasTooltip() {
				return this.tooltipText.length > 0
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

			tooltipText() {
				const { tooltip } = this

				return 'undefined' === typeof tooltip ? '' : tooltip
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

	.tooltip-button {
		background: none;
		border: 0;
		margin-left: 2px;
		padding: 0;
		vertical-align: middle;
	}

	.tooltip-button:hover {
		border: 0;
	}

	.tooltip-button:after {
		content: "\f348";
		font-family: 'dashicons';
		font-size: 16px;
	}

	.tooltip {
	  display: block !important;
	  z-index: 10000;
	  width: 250px;
	}

	.tooltip .tooltip-inner {
	  background: black;
	  color: white;
	  border-radius: 4px;
	  padding: 5px 10px 4px;
	}

	.tooltip .tooltip-arrow {
	  width: 0;
	  height: 0;
	  border-style: solid;
	  position: absolute;
	  margin: 5px;
	  border-color: black;
	  z-index: 1;
	}

	.tooltip[x-placement^="right"] {
	  margin-left: 5px;
	}

	.tooltip[x-placement^="right"] .tooltip-arrow {
	  border-width: 5px 5px 5px 0;
	  border-left-color: transparent !important;
	  border-top-color: transparent !important;
	  border-bottom-color: transparent !important;
	  left: -5px;
	  top: calc(50% - 5px);
	  margin-left: 0;
	  margin-right: 0;
	}

	.tooltip.popover .popover-inner {
	  background: #f9f9f9;
	  color: black;
	  padding: 24px;
	  border-radius: 2px;
	  box-shadow: 0 5px 30px rgba(black, .1);
	}

	.tooltip.popover .popover-arrow {
	  border-color: #f9f9f9;
	}

	.tooltip[aria-hidden='true'] {
	  visibility: hidden;
	  opacity: 0;
	  transition: opacity .15s, visibility .15s;
	}

	.tooltip[aria-hidden='false'] {
	  visibility: visible;
	  opacity: 1;
	  transition: opacity .15s;
	}
</style>
