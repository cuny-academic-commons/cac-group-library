<template>
	<div class="library-item">
		{{ title() }}
	</div>
</template>

<script>
	export default {
		methods: {
			getItem() {
				return this.$store.state.libraryItems[ this.itemId ]
			},

			title() {
				return this.getItem().title
			}
		},

		props: [
			'itemId'
		]
	}
</script>

<style>

</style>
