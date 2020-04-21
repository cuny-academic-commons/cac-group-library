<template>
	<button
		class="description-toggle"
		v-on:click="onClick()"
	>{{buttonText}}</button>
</template>

<script>
	export default {
		computed: {
			buttonText() {
				return this.showDescriptions ? 'Collapse all' : 'Expand all'
			},

			showDescriptions: {
				get() {
					return this.$store.state.showDescriptions
				},

				set( value ) {
					this.$store.commit(
						'setShowDescriptions',
						{
							value
						}
					)
				}
			}
		},

		methods: {
			onClick() {
				this.showDescriptions = ! this.showDescriptions
			}
		}
	}
</script>

<style>
button.description-toggle,
button.description-toggle:hover {
	background: 0;
	border: 0;
	color: #2ca0c3;
}

button.description-toggle:hover {
	text-decoration: underline;
}

</style>
