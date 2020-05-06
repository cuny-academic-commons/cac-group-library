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

				this.deleteInProgress = true

				this.$store.dispatch(
					'deleteItem',
					this.itemId
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
</style>
