<template>
	<FilterDropdown
		name='itemType'
		title='Filter: '
		:opts="itemTypesWithItems()"
		:getCurrentCallback="getCurrentCallback"
		:setCurrentCallback="setCurrentCallback"
	/>
</template>

<script>
	import FilterDropdown from './FilterDropdown.vue'

	export default {
		components: {
			FilterDropdown
		},

		computed: {
			itemIds() {
				return this.$store.state.libraryItemIds
			},

			itemTypes() {
				return [
					{ code: 'any', label: 'No Filter' },
					{ code: 'bp_group_document', label: 'Files' },
					{ code: 'bp_doc', label: 'Docs' },
					{ code: 'cacsp_paper', label: 'Papers' },
					{ code: 'forum_attachment', label: 'Forum Attachments' },
					{ code: 'external_link', label: 'External Links' }
				]
			},
		},

		methods: {
			getCurrentCallback() {
				const currentItemType = this.$store.state.route.query.hasOwnProperty( 'itemType' ) ? decodeURIComponent( this.$store.state.route.query.itemType ) : 'any'
				return this.itemTypes.filter( itemType => currentItemType === itemType.code )
			},

			getItem( itemId ) {
				return this.$store.state.libraryItems[ itemId ]
			},

			itemTypesWithItems() {
				const { itemIds, getItem } = this

				return this.itemTypes.filter(
					function( itemType ) {
						if ( 'any' === itemType.code ) {
							return true
						}

						let itemOfTypeExists = false
						let itemId

						for ( var i in itemIds ) {
							itemId = itemIds[ i ]

							if ( itemType.code === getItem( itemId ).item_type ) {
								itemOfTypeExists = true
								break
							}
						}

						return itemOfTypeExists
					}
				)
			},

			setCurrentCallback( payload ) {
				const newQuery = Object.assign( {}, this.$route.query, {
					itemType: encodeURIComponent( payload.code ),
					page: 1
				} )

				this.$router.push( {
					path: '/',
					query: newQuery
				} )
			}
		}
	}
</script>
