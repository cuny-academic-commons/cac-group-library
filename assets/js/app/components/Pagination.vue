<template>
	<span>
		<div class="pagination-text">
			Viewing {{startNumber()}} to {{endNumber()}} (of {{totalNumber()}} items)
		</div>

		<div
			class="pagination-links"
			v-if="hasMoreThanOnePage() && showPagLinks"
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
				const currentFolder = this.$store.state.route.query.hasOwnProperty( 'folder' ) ? decodeURIComponent( this.$store.state.route.query.folder ) : 'any';

				this.$router.push({
					path: '/',
					query: {
						folder: currentFolder,
						page: pageNum
					}
				})

				this.$store.commit( 'refresh' )
			},

			currentPage() {
				return this.$store.state.route.query.hasOwnProperty( 'page' ) ? Number( this.$store.state.route.query.page ) : 1
			},

			endNumber() {
				const pagEnd = this.startNumber() + this.perPage - 1

				return ( pagEnd > this.totalNumber() ) ? this.totalNumber() : pagEnd
			},

			hasMoreThanOnePage() {
				return this.pageCount() > 1
			},

			onChange(e ) {
				console.log(e)
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
		},

		props: {
			showPagLinks: {
				type: Boolean
			}
		}
	}
</script>

<style>

.pagination-links ul {
	display: flex;
	list-style-type: none;
}

.pagination-links li {
	padding: 0 8px;
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
