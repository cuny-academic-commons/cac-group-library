<template>
	<div :class="className">
		<label
			:for="fieldId"
		>{{fieldLabel}}</label>

		<input
			:id="fieldId"
			:name="fieldId"
			:required="required"
			:type="fieldValidationType"
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
			}
		},

		props: {
			description: String,
			fieldId: String,
			fieldLabel: String,
			fieldName: String,
			fieldType: String,
			fieldValidationType: String,
			formName: String,
			required: Boolean,
		}
	}
</script>

<style>
.add-new-field.has-error input {
	border-color: #f00;
}
.add-new-field.has-error .field-error {
	background: #db1717;
	border: 1px solid #a71a1a;
	border-radius: 2px;
	color: #fff;
	padding: 10px;
	margin-top: 7px;
	width: 80%;
}
</style>
