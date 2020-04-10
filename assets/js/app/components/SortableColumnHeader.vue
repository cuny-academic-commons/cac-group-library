<template>
	<div :class='itemClass()'>
		<span
			v-on:click='onHeaderClick'
		>{{ this.labelText() }}</span>
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
.group-library-column-header span {
	color: #666;
	cursor: pointer;
	display: inline-block;
	line-height: 28px;
	position: relative;
	text-decoration: none;
	width: 100%;
}

.group-library-column-header span:after {
	font-family: "dashicons";
	font-size: 10px;
	line-height: 28px;
	margin-left: 8px;
	position: absolute;
}

.group-library-column-header.default-sort-order-asc:hover span:after {
	content: "\f342";
}

.group-library-column-header.default-sort-order-desc:hover span:after {
	content: "\f346";
}

.group-library-column-header.is-current-sort-order-desc span:after {
	content: "\f346";
}

.group-library-column-header.is-current-sort-order-asc span:after {
	content: "\f342";
}

.group-library-column-header.is-current-sort-order-desc:hover span:after {
	content: "\f342";
}

.group-library-column-header.is-current-sort-order-asc:hover span:after {
	content: "\f346";
}
</style>
