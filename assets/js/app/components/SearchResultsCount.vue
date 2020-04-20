<template>
	<div class="search-results-count">{{countText}}</div>
</template>

<script>
	export default {
		computed: {
			count() {
				return this.$store.state.filteredItemIds.length
			},

			countText() {
				switch ( this.count ) {
					case 1 :
						return '1 Result'

					case 0 :
						return 'No Results'

					default :
						return this.count + ' Results'
				}
			}
		}
	}
</script>

<style>
	.search-results-count {
		font-size: 16px;
	}
</style>
