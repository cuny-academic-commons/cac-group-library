<template>
	<button
		class="description-toggle"
		v-on:click="onClick()"
	>{{buttonText}} <img class="description-toggle-image" :src="buttonImg" /></button>
</template>

<script>
	export default {
		computed: {
			buttonImg() {
				const base = window.CACGroupLibrary.imgUrlBase
				const filename = this.showDescriptions ? 'collapse' : 'expand'

				return base + '/' + filename + '.svg'
			},

			buttonText() {
				return this.showDescriptions ? 'Hide details' : 'Show details'
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
	line-height: 14px;
}

button.description-toggle:hover {
	text-decoration: underline;
}

.description-toggle-image {
	height: 12px;
	margin-left: 2px;
	vertical-align: middle;
}
</style>
