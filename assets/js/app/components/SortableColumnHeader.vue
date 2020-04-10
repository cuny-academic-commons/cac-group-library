<template>
	<div :class='itemClass()'>
		<a :href='url()'>{{ this.labelText() }}</a>
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
.group-library-column-header a:link,
.group-library-column-header a:visited,
.group-library-column-header a {
	color: #666;
	display: inline-block;
	line-height: 28px;
	position: relative;
	text-decoration: none;
	width: 100%;
}

.group-library-column-header a:after {
	font-family: "dashicons";
	font-size: 10px;
	line-height: 28px;
	margin-left: 8px;
	position: absolute;
}

.group-library-column-header.default-sort-order-asc:hover a:after {
	content: "\f342";
}

.group-library-column-header.default-sort-order-desc:hover a:after {
	content: "\f346";
}

.group-library-column-header.is-current-sort-order-desc a:after {
	content: "\f346";
}

.group-library-column-header.is-current-sort-order-asc a:after {
	content: "\f342";
}

.group-library-column-header.is-current-sort-order-desc:hover a:after {
	content: "\f342";
}

.group-library-column-header.is-current-sort-order-asc:hover a:after {
	content: "\f346";
}
</style>
