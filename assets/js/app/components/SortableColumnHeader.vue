<template>
	<div :class='itemClass()'>
		{{ this.labelText() }}
	</div>
</template>

<script>
	export default {
		computed: {
			currentSort() {
				return this.$store.state.currentSort
			},
			currentSortOrder() {
				return this.$store.state.currentSortOrder
			}
		},

		methods: {
			itemClass() {
				return 'group-library-item-' + this.name
			},
			labelText() {
				return this.label
			}
		},

		props: {
			label: String,
			name: String
		}
	}
</script>

<style>

</style>
