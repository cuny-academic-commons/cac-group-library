<template>
	<button
		:class="{ 'add-new-submit-button': true, 'submit-in-progress': submitInProgress }"
		v-bind:style="backgroundStyles"
		:disabled="! formIsValid()"
		@click.prevent="onSubmitClick()"
	>{{ buttonText }}</button>
</template>

<script>
	import FormValidation from '../mixins/FormValidation'

	export default {
		computed: {
			backgroundStyles() {
				const { imgUrlBase } = window.CACGroupLibrary;

				if ( this.submitInProgress ) {
					return {
						'background-image': 'url( ' + imgUrlBase + 'spinner.gif )',
						'background-position': 'center right 8px',
						'background-repeat': 'no-repeat',
					}
				} else {
					return {}
				}
			},

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
								'setSuccessMessage',
								{
									value: json.message
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
						})
					}
				}).catch( function( ex ) {
					console.log( 'failed', ex )
				})
			},

			formIsValid() {
				return this.isFormValid( this.formName )
			}
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

<style>
.add-new-submit-button {
	background: #1C576C;
	border: none;
	color: #fff;
	font-size: 16px;
	padding: 9px 15px;
}

.add-new-submit-button:hover {
	background: #022d3c;
	border: none;
	color: #fff;
}

.add-new-submit-button:disabled {
	opacity: .7;
}

.add-new-submit-button:disabled:hover {
	background: #1C576C;
}

.add-new-submit-button.submit-in-progress {
	padding-right: 30px;
}

.success-message {
	background: #449621;
	border-radius: 4px;
	color: #fff;
	margin-top: 10px;
	padding: 8px 20px;
}

</style>
