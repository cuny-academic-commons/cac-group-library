<template>
	<div
		:class='itemClass()'
		role="columnheader"
		:aria-sort='ariaSort()'
	>
		<button
			type="button"
			class="sortable-column-header-button"
			v-on:click='onHeaderClick'
			:aria-label='ariaLabel()'
		>{{ this.labelText() }}</button>
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
				let classes = []

				classes.push( 'group-library-column-header' )
				classes.push( 'group-library-item-' + this.name )
				classes.push( 'default-sort-order-' + this.defaultSortOrder )

				if ( this.name === this.currentSort ) {
					classes.push( 'is-current-sort' )
					classes.push( 'is-current-sort-order-' + this.currentSortOrder )
				}

				return classes.join( ' ' )
			},

			labelText() {
				return this.label
			},

			ariaSort() {
				if ( this.name !== this.currentSort ) {
					return 'none'
				}

				return 'asc' === this.currentSortOrder ? 'ascending' : 'descending'
			},

			ariaLabel() {
				if ( this.name !== this.currentSort ) {
					return `Sort by ${this.label}`
				}

				const nextOrder = 'asc' === this.currentSortOrder ? 'descending' : 'ascending'

				return `Sort by ${this.label}, currently sorted ${'asc' === this.currentSortOrder ? 'ascending' : 'descending'}. Activate to sort ${nextOrder}.`
			},

			onHeaderClick() {
				let newSort = ''
				let newSortOrder = ''

				newSort = this.name

				if ( newSort === this.currentSort ) {
					newSortOrder = 'asc' === this.currentSortOrder ? 'desc' : 'asc'
				} else {
					newSortOrder = this.defaultSortOrder
				}

				this.$store.commit(
					'setSort',
					{
						newSort,
						newSortOrder
					}
				)

				const { query } = this.$store.state.route
				if ( query.hasOwnProperty( 'page' ) && query.page > 1 ) {
					this.$router.push( {
						path: '/',
						query: Object.assign( {}, query, { page: 1 } ),
					} )
				}

				this.$store.commit( 'refreshFilteredItemIds' )
			},

			url() {
				return ''
			}
		},

		props: {
			defaultSortOrder: String,
			label: String,
			name: String
		}
	}
</script>

<style>
.group-library-column-header {
	color: var(--dark-grey);
	line-height: 28px;
}

#buddypress .group-library-column-header button.sortable-column-header-button {
	background: none;
	border: none;
	color: inherit;
	cursor: pointer;
	font: inherit;
	line-height: 28px;
	margin: 0;
	padding: 0;
	position: relative;
	text-align: left;
	text-decoration: none;
	width: 100%;
}

.group-library-column-header .sortable-column-header-button:after {
	font-family: "dashicons";
	font-size: 10px;
	line-height: 28px;
	margin-left: 8px;
	position: absolute;
}

.group-library-column-header.default-sort-order-asc:hover .sortable-column-header-button:after {
	content: "\f342";
}

.group-library-column-header.default-sort-order-desc:hover .sortable-column-header-button:after {
	content: "\f346";
}

.group-library-column-header.is-current-sort-order-desc .sortable-column-header-button:after {
	content: "\f346";
}

.group-library-column-header.is-current-sort-order-asc .sortable-column-header-button:after {
	content: "\f342";
}

.group-library-column-header.is-current-sort-order-desc:hover .sortable-column-header-button:after {
	content: "\f342";
}

.group-library-column-header.is-current-sort-order-asc:hover .sortable-column-header-button:after {
	content: "\f346";
}
</style>
