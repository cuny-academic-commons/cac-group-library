module.exports = {
	methods: {
		getFieldError( fieldName ) {
				const { validationErrors } = this.$store.state

				if ( validationErrors.hasOwnProperty( fieldName ) ) {
					return validationErrors[ fieldName ]
				}

				return ''
		},

		fieldHasBeenVisited( formName, fieldName ) {
			const { visitedFields } = this.$store.state
			const visitedFieldsOfForm = visitedFields.hasOwnProperty( formName ) ? visitedFields[ formName ] : []
			return -1 !== visitedFieldsOfForm.indexOf( fieldName )
		},

		validateForm( formName, validateAll = false ) {
			const invalidNodes = document.querySelectorAll( `.add-new-form-${formName} :invalid` )
			const vm = this;

			this.$store.commit( 'resetValidationErrors' )

			if ( invalidNodes.length > 0 ) {
				invalidNodes.forEach( node => {
					if ( ! validateAll && ! vm.fieldHasBeenVisited( formName, node.name ) ) {
						return;
					}

					vm.$store.commit(
						'setValidationError',
						{
							nodeName: node.name,
							message: node.validationMessage
						}
					)
				} )

				return false
			} else {
				return true
			}

		}
	}
}

