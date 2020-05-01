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

		isFormValid( formName ) {
			// Validate by looking at state rather than using browser validation.
			switch ( formName ) {
				case 'bpGroupDocument' :
					return this.hasValue( formName, 'title'	) && this.hasValue( formName, 'file' )
				break;

				case 'bpDoc' :
					return this.hasValue( formName, 'title'	) && this.hasValue( formName, 'content' )
				break;

				case 'externalLink' :
					return this.hasValue( formName, 'title'	) && this.hasValue( formName, 'url' ) && this.fieldValueIsUrl( formName, 'url' )
				break;
			}
		},

		fieldValueIsUrl( formName, fieldName ) {
			const value = this.$store.state.forms[ formName ][ fieldName ]

			if ( 'string' !== typeof value ) {
				return false
			}

			const pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
				'((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
				'((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
				'(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
				'(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
				'(\\#[-a-z\\d_]*)?$','i'); // fragment locator

			return !!pattern.test( value );
		},

		hasValue( formName, fieldName ) {
			const value = this.$store.state.forms[ formName ][ fieldName ]

			switch ( typeof value ) {
				case 'object' :
					return true

				case 'string' :
					return value.length > 0

				default :
					return false
			}
		},

		validateField( formName, fieldName ) {
			const key = formName + '-' + fieldName
			const vm = this

			switch ( formName ) {
				case 'bpGroupDocument' :
					switch ( fieldName ) {
						case 'title' :
						case 'file' :
							if ( ! this.hasValue( formName, fieldName ) ) {
								vm.$store.commit(
									'setValidationError',
									{
										nodeName: key,
										message: 'Please enter a value for this field.'
									}
								)
							}
					}
					break;

				case 'bpDoc' :
					switch ( fieldName ) {
						case 'title' :
						case 'file' :
							if ( ! this.hasValue( formName, fieldName ) ) {
								vm.$store.commit(
									'setValidationError',
									{
										nodeName: key,
										message: 'Please enter a value for this field.'
									}
								)
							}
					}
					break;

				case 'externalLink' :
					switch ( fieldName ) {
						case 'url' :
							if ( ! this.fieldValueIsUrl( formName, fieldName ) ) {
								vm.$store.commit(
									'setValidationError',
									{
										nodeName: key,
										message: 'Please enter a valid URL.'
									}
								)
							}

						// Fall through.
						case 'title' :
							if ( ! this.hasValue( formName, fieldName ) ) {
								vm.$store.commit(
									'setValidationError',
									{
										nodeName: key,
										message: 'Please enter a value for this field.'
									}
								)
							}
						break
				}

				break
			}
		},

		fileIsInvalid( formName ) {
			const { maxUploadSize, maxUploadSizeFormatted, uploadFiletypes } = window.CACGroupLibrary
			const form = this.$store.state.forms[ formName ]
			const { file } = form

			if ( ! file ) {
				return ''
			}

			if ( file.size > maxUploadSize ) {
				return 'Your file is too large. Max upload size: ' + maxUploadSizeFormatted
			}

			const fileExtension = file.name.split('.').pop();
			if ( -1 === uploadFiletypes.indexOf( fileExtension ) ) {
				return 'File type not allowed.'
			}

			return ''
		}
	},
}

