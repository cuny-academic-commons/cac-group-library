<template>
	<button
		:class="{ 'delete-button': true, 'delete-in-progress': deleteInProgress }"
		:disabled="disabled()"
		v-bind:style="backgroundStyles"
		@click.prevent="onClick()"
	>
		Delete
	</button>
</template>

<script>
	import AjaxTools from '../mixins/AjaxTools.js'
	import 'vuejs-dialog/dist/vuejs-dialog.min.css';

	export default {
		computed: {
			backgroundStyles() {
				const { imgUrlBase } = window.CACGroupLibrary;

				if ( this.deleteInProgress ) {
					return {
						'background-image': 'url( ' + imgUrlBase + 'spinner.gif )',
						'background-position': 'center right 8px',
						'background-repeat': 'no-repeat',
					}
				} else {
					return {}
				}
			},
		},

		methods: {
			disabled() {
				return this.deleteInProgress || this.$store.state.submitInProgress
			},

			onClick: function() {
				const app = this

				app.deleteInProgress = true

				const dialogOptions = {
					cancelText: 'Cancel',
					customClass: 'delete-item-dialog',
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
.delete-button {
	background: #1C576C;
	border: none;
	color: #fff;
	font-size: 16px;
	padding: 9px 15px;
}

.delete-button:hover {
	background: #022d3c;
	border: none;
	color: #fff;
}

.delete-button:disabled {
	opacity: .7;
}

.delete-button:disabled:hover {
	background: #1C576C;
}

.delete-button.delete-in-progress {
	padding-right: 30px;
}

.delete-item-dialog button {
	background: #1C576C;
	border: none;
	color: #fff;
	font-size: 16px;
	font-weight: normal;
	padding: 9px 15px;
}

.delete-item-dialog button:hover {
	background: #022d3c;
	border: none;
	color: #fff;
}
</style>
