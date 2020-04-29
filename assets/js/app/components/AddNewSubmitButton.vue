<template>
	<button
		class="add-new-submit-button"
		:disabled="! validated"
		@click.prevent="onSubmitClick()"
	>{{ buttonText }}</button>
</template>

<script>
	import FormValidation from '../mixins/FormValidation'

	export default {
		computed: {
			formData() {
				return this.$store.state.forms[ this.formName ]
			},

			submitInProgress: {
				get() {
					return this.$store.state.submitInProgress
				},

				set( value ) {
					this.$store.commit( 'setSubmitInProgress', { value } )
				}
			},

			validated() {
				return this.validateForm( this.formName )
			}
		},

		methods: {
			onSubmitClick: function() {
				const app = this

				this.submitInProgress = true

				this.$store.dispatch(
					'submitAddNew',
					this.formData
				)
				.then( function( response ) {
					return response.json()
				}).then( function( json ) {
					if ( json.success ) {
						app.$store.commit( 'resetForms' );

						app.$store.dispatch( 'refetchItems' )
						.then( function() {
							app.$store.commit(
								'setSort',
								{
									newSort: 'date',
									newSortOrder: 'desc',
								}
							)

							app.$store.commit( 'refreshFilteredItemIds' )

							app.$router.push( { path: '/' } )
						})

						app.submitInProgress = false
					}
				}).catch( function( ex ) {
					console.log( 'failed', ex )
				})
			},
		},

		mixins: [
			FormValidation
		],

		props: {
			buttonText: String,
			formName: String
		}
	}
</script>
