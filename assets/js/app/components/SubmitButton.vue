<template>
	<button
		:class="{ 'add-new-submit-button': true, 'submit-in-progress': submitInProgress, 'cac-button': true }"
		v-bind:style="backgroundStyles( 'submit' )"
		:disabled="disabled()"
		@click.prevent="onSubmitClick()"
	>{{ buttonText }}</button>
</template>

<script>
	import AjaxTools from '../mixins/AjaxTools'
	import FormValidation from '../mixins/FormValidation'

	export default {
		computed: {
			formData() {
				return this.$store.state.forms[ this.formName ]
			},
		},

		methods: {
			disabled() {
				return ! this.formIsValid() || this.submitInProgress || this.$store.state.deleteInProgress
			},

			onSubmitClick: function() {
				const app = this

				this.submitInProgress = true

				this.$store.dispatch(
					'submitItem',
					this.formData
				)
				.then( function( response ) {
					return response.json()
				}).then( function( json ) {
					if ( json.success ) {
						// Wait for both refetch operations to complete
						Promise.all([
							app.$store.dispatch( 'refetchItems' ),
							app.$store.dispatch( 'fetchFoldersOfGroup' )
						]).then( function() {
							// Recalculate counts now that both items and folders are updated
							app.$store.commit( 'calculateFolderCounts' )
							app.postAjaxFormActions( {
								message: json.message
							} )
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
			AjaxTools,
			FormValidation,
		],

		props: {
			buttonText: String,
			formName: String
		}
	}
</script>

<style>
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
