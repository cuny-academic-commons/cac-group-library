<template>
	<FilterDropdown
		name='folder'
		title='Select Folder'
		:opts="folders()"
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
		},

		methods: {
			folders() {
				const { itemIds, getItem } = this

				let folders = []

				itemIds.map(
					function( itemId ) {
						const theItem = getItem( itemId )
						for ( var i in theItem.folders ) {
							folders.push( {
								code: theItem.folders[ i ].slug,
								label: theItem.folders[ i ].name
							} )
						}
					}
				)

				let folderSlugs = []
				let uniqueFolders = folders.filter(
					function( folder ) {
						if ( -1 === folderSlugs.indexOf( folder.code ) ) {
							folderSlugs.push( folder.code )
							return true
						}

						return false
					}
				)

				uniqueFolders.sort(
					function( a, b ) {
						return a.label.localeCompare( b.label )
					}
				)

				const anyFolder = { code: 'any', label: 'Any folder' }

				uniqueFolders.unshift( anyFolder )

				return uniqueFolders
			},

			getCurrentCallback() {
				const { currentFolder } = this.$store.state
				return this.folders().filter( folder => currentFolder === folder.code )
			},

			getItem( itemId ) {
				return this.$store.state.libraryItems[ itemId ]
			},

			setCurrentCallback( payload ) {
				this.$store.commit(
					'setCurrentFolder',
					{
						value: payload.code
					}
				)
			}
		}
	}
</script>
