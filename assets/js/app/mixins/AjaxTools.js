module.exports = {
	computed: {
		deleteInProgress: {
			get() {
				return this.$store.state.deleteInProgress
			},

			set( value ) {
				this.$store.commit( 'setDeleteInProgress', { value } )
			}
		},

		submitInProgress: {
			get() {
				return this.$store.state.submitInProgress
			},

			set( value ) {
				this.$store.commit( 'setSubmitInProgress', { value } )
			}
		},
	},

	methods: {
		backgroundStyles( buttonType ) {
			const { imgUrlBase } = window.CACGroupLibrary;

			if ( this.deleteInProgress && 'delete' === buttonType || this.submitInProgress && 'submit' === buttonType ) {
				return {
					'background-image': 'url( ' + imgUrlBase + 'spinner.gif )',
					'background-position': 'center right 8px',
					'background-repeat': 'no-repeat',
					'padding-right': '30px',
				}
			} else {
				return {}
			}
		},

		postAjaxFormActions( payload ) {
			const { message } = payload
			const app = this

			app.$store.commit( 'resetForms' );

			app.$store.commit(
				'setSuccessMessage',
				{
					value: message
				}
			)

			setTimeout( function() {
				app.$store.commit(
					'setSuccessMessage',
					{
						value: ''
					}
				)
			}, 5000 )

			app.$store.commit(
				'setSort',
				{
					newSort: 'date',
					newSortOrder: 'desc',
				}
			)

			app.$store.commit( 'refreshFilteredItemIds' )

			app.$router.push( { path: '/' } )

			app.submitInProgress = false
			app.deleteInProgress = false
		}
	}
}
