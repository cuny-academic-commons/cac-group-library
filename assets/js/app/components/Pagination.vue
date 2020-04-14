<template>
	<span>
		Viewing {{startNumber()}} to {{endNumber()}} (of {{totalNumber()}} items)
	</span>
</template>

<script>
	export default {
		computed: {
			perPage() {
				return 3
			}
		},

		methods: {
			currentPage() {
				return this.$store.state.currentPage
			},

			endNumber() {
				const pagEnd = this.startNumber() + this.perPage - 1

				return ( pagEnd > this.totalNumber() ) ? this.totalNumber() : pagEnd
			},

			startNumber() {
				return ( this.perPage * ( this.currentPage() - 1 ) ) + 1
			},

			totalNumber() {
				return this.$store.state.filteredItemIds.length
			}
		}
	}
</script>

<style>

</style>
