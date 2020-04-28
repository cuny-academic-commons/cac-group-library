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
			validated() {
				return true
				return this.validateForm( 'externalLink' )
			}
		},

		data() {
			return {
				submitInProgress: false,
			}
		},

		methods: {
			onSubmitClick: function() {
				const app = this

				this.submitInProgress = true

				this.$store.dispatch( 'submitAddNew' )
					.then( function( response ) {
						return response.json()
					}).then( function( json ) {
						app.submitInProgress = false
						console.log(json)
						if ( json.success ) {
							app.$store.commit( 'goToPanel', {
								panelIndex: 3
							} )
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
