<template>
	<span>
		<div class="pagination-text">
			Viewing {{startNumber()}} to {{endNumber()}} (of {{totalNumber()}} items)
		</div>

		<div class="pagination-links">
			<Paginate
				:clickHandler="clickHandler"
				:pageCount="pageCount()"
				nextText="&raquo;"
				prevText="&laquo;"
				v-model="page">
			</Paginate>

		</div>
	</span>
</template>

<script>
	import Paginate from 'vuejs-paginate'

	export default {
		components: {
			Paginate
		},

		computed: {
			perPage() {
				return this.$store.state.perPage
			}
		},

		data() {
			return {
				page: this.currentPage()
			}
		},

		methods: {
			clickHandler( pageNum ) {
				this.$store.commit(
					'setCurrentPage',
					{
						value: pageNum
					}
				)

				this.$store.commit( 'refresh' )
			},

			currentPage() {
				return this.$store.state.currentPage
			},

			endNumber() {
				const pagEnd = this.startNumber() + this.perPage - 1

				return ( pagEnd > this.totalNumber() ) ? this.totalNumber() : pagEnd
			},

			pageCount() {
				return Math.ceil( this.totalNumber() / this.perPage )
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
