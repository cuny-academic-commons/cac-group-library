<template>
	<span>
		<div class="pagination-text">
			Viewing {{startNumber()}} to {{endNumber()}} (of {{totalNumber()}} items)
		</div>

		<div 
			class="pagination-links"
			v-if="hasMoreThanOnePage()"
		>
			<Paginate
				:clickHandler="clickHandler"
				:pageCount="pageCount()"
				breakViewClass="pagination-break"
				nextText="&raquo;"
				prevClass="pagination-prev"
				prevText="&laquo;"
				nextClass="pagination-next"
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
			
			hasMoreThanOnePage() {
				return this.pageCount() > 1
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
.group-library-pagination {
	position: relative;
}

.pagination-links {
	font-size: 12px;
	position: absolute;
	right: 0;
	top: 0;
}

.pagination-links ul {
	list-style-type: none;
}

.pagination-links li {
	display: block;
	float: left;
	padding: 0 4px;
}

.pagination-links li a {
	text-decoration: underline;
}

.pagination-links li a:hover {
	text-decoration: none;
}

.pagination-links li.pagination-next.disabled,
.pagination-links li.pagination-prev.disabled {
	display: none;
}

.pagination-links li.pagination-break a {
	text-decoration: none;
}

.pagination-links li.active a {
	color: #000;
	text-decoration: none;
}
</style>
