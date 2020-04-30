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

		addNewFolderIsInvalid( formName ) {
			const form = this.$store.state.forms[ formName ]
			const { folder, newFolderTitle } = form

			if ( '_addNew' !== folder ) {
				return false;
			}

			return newFolderTitle.length === 0
		},

		validateForm( formName, validateAll = false ) {
			const invalidNodes = document.querySelectorAll( `.add-new-form-${formName} :invalid` )
			const vm = this;

			this.$store.commit( 'resetValidationErrors' )

			// Special case: _addNew and corresponding field.
			const showAddNewFolderError = this.addNewFolderIsInvalid( formName ) && this.fieldHasBeenVisited( formName, 'add-new-folder-input' )

			// All valid.
			if ( ! showAddNewFolderError && invalidNodes.length === 0 ) {
				return true
			}

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
			}

			if ( showAddNewFolderError ) {
				vm.$store.commit(
					'setValidationError',
					{
						nodeName: 'add-new-folder-input',
						message: 'Please provide a name for the new folder',
					}
				)
			}

			return false
		}
	}
}

