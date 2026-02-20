<template>
	<button
		:class="{ 'delete-button': true, 'delete-in-progress': deleteInProgress, 'cac-button': true, 'cac-button-secondary': true }"
		:disabled="disabled()"
		v-bind:style="backgroundStyles( 'delete' )"
		@click.prevent="onClick()"
	>
		Delete
	</button>
</template>

<script>
	import AjaxTools from '../mixins/AjaxTools.js'
	import 'vuejs-dialog/dist/vuejs-dialog.min.css';

	export default {
		methods: {
			disabled() {
				return this.deleteInProgress || this.$store.state.submitInProgress
			},

			onClick: function() {
				const app = this

				app.deleteInProgress = true

				const dialogOptions = {
					cancelText: 'Cancel',
					customClass: 'group-library-dialog delete-item-dialog',
					okText: 'Delete',
				}

				app.$dialog
					.confirm( 'Item will be permanently deleted. Are you sure you want to continue?', dialogOptions )
					.then( function( dialog ) {
						app.$store.dispatch(
							'deleteItem',
							app.itemId
						)
						.then( function( response ) {
							return response.json()
						}).then( function( json ) {
							if ( json.success ) {
								app.$store.dispatch( 'refetchItems' )
								.then( function() {
									app.postAjaxFormActions( {
										message: json.message
									} )
								})
							}
						}).catch( function( ex ) {
							console.log( 'failed', ex )
						})
					})
					.catch( function( dialog ) {
						app.deleteInProgress = false
					})
			},
		},

		mixins: [
			AjaxTools,
		],

		props: {
			itemId: Number
		}
	}
</script>

<style>
.delete-button:disabled {
	opacity: .7;
}

.delete-button:disabled:hover {
	background: #1C576C;
}
</style>
