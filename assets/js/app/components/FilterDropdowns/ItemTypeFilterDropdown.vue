<template>
	<FilterDropdown
		name='itemType'
		title='Select Item Type'
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
					{ code: 'any', label: 'Any kind' },
					{ code: 'bp_group_document', label: 'Files' },
					{ code: 'bp_doc', label: 'Docs' },
					{ code: 'papers', label: 'Papers' },
					{ code: 'atts', label: 'Forum Attachments' },
					{ code: 'links', label: 'External Links' }
				]
			},
		},

		methods: {
			getCurrentCallback() {
				const { currentItemType } = this.$store.state
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
				this.$store.commit(
					'setCurrentItemType',
					{
						value: payload.code
					}
				)

				this.$store.commit( 'refresh' )
			}
		}
	}
</script>
