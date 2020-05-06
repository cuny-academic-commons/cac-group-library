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
