<template>
	<span>
		<div
			class="group-library-folder-filter-buttons"
			v-if="$mq === 'desktop'"
		>
			<ul class="folder-buttons">
				<li>
					<button
						:class="folderButtonClass('')"
						v-on:click="folderButtonClick('')"
					>{{folderButtonText('')}}</button>
				</li>

				<li v-for="folder in foldersOfGroup">
					<button
						:class="folderButtonClass(folder)"
						v-on:click="folderButtonClick(folder)"
					>{{folderButtonText(folder)}}</button>
				</li>

				<li v-if="canEditFolders">
					<router-link
						class="manage-folders-link"
						to='/editFolders'
					>Manage folders</router-link>
				</li>

			</ul>
		</div>

		<div
			class="group-library-folder-filter-dropdown"
			v-else
		>
			<FilterDropdown
				name='folder'
				title='Select Folder'
				:opts="folders()"
				:getCurrentCallback="getCurrentCallback"
				:setCurrentCallback="setCurrentCallback"
			/>
		</div>
	</span>
</template>

<script>
	import FilterDropdown from './FilterDropdown.vue'

	export default {
		components: {
			FilterDropdown
		},

		computed: {
			canEditFolders() {
				return this.$store.state.canEditFolders
			},

			folderCounts() {
				return this.$store.state.folderCounts
			},

			foldersOfGroup() {
				return this.$store.state.foldersOfGroup
			},

			itemIds() {
				return this.$store.state.libraryItemIds
			},
		},

		methods: {
			folderButtonClass( folder ) {
				let classes = [ 'folder-button' ]

				if ( this.isCurrentFolder( folder ) ) {
					classes.push( 'current-folder' )
				}

				return classes.join( ' ' )
			},

			folderButtonClick( folder ) {
				let folderQuery = Object.assign( {}, this.$route.query )

				folderQuery.page = 1

				let folderQueryArgs
				if ( '' === folder ) {
					delete folderQuery.folder
				} else {
					folderQuery.folder = encodeURIComponent( folder )
				}

				this.$router.push( {
					path: '/',
					query: folderQuery
				} )

				this.$store.commit( 'refresh' )
			},

			folderButtonText( folder ) {
				const { folderCounts } = this

				if ( '' === folder ) {
					return 'ALL ITEMS (' + this.$store.state.libraryItemIds.length + ')'
				} else {

					const folderCount = folderCounts.hasOwnProperty( folder ) ? folderCounts[ folder ] : 0

					return this.truncate( folder ) + ' (' + folderCount + ')'
				}
			},

			folders() {
				const { folderCounts, itemIds, getItem } = this
				const vm = this

				const { foldersOfGroup } = this.$store.state

				let folderCount
				let folderObjects = foldersOfGroup.map(
					function( folder ) {
						folderCount = folderCounts.hasOwnProperty( folder ) ? folderCounts[ folder ] : 0

						const folderLabel = folder + ' (' + folderCount + ')'

						return {
							code: folder,
							label: folderLabel,
						}
					}
				)

				const allItemsLabel = 'ALL ITEMS (' + this.$store.state.libraryItemIds.length + ')'
				const anyFolder = { code: 'any', label: allItemsLabel }
				folderObjects.unshift( anyFolder )

				if ( this.canEditFolders && foldersOfGroup.length > 0 ) {
					const editFolders = { code: '_edit', label: 'Edit folders' }
					folderObjects.push( editFolders )
				}

				return folderObjects
			},

			getCurrentCallback() {
				const currentFolder = this.$store.state.route.query.hasOwnProperty( 'folder' ) ? decodeURIComponent( this.$store.state.route.query.folder ) : 'any'
				return this.folders().filter( folder => currentFolder === folder.code )
			},

			getItem( itemId ) {
				return this.$store.state.libraryItems[ itemId ]
			},

			isCurrentFolder( folder ) {
				const currentFolder = this.$store.state.route.query.hasOwnProperty( 'folder' ) ? decodeURIComponent( this.$store.state.route.query.folder ) : 'any'

				return ( '' === folder && 'any' === currentFolder ) || folder === currentFolder
			},

			setCurrentCallback( payload ) {
				if ( '_edit' === payload.code ) {
					this.$router.push( {
						path: '/editFolders'
					} )
				} else {
					const newQuery = Object.assign( {}, this.$route.query, {
						folder: encodeURIComponent( payload.code ),
						page: 1
					} )

					this.$router.push( {
						path: '/',
						query: newQuery
					} )
				}
			},

			truncate( str, max = 5 ) {
				const array = str.trim().split(' ')
				const ellipsis = array.length > max ? '...' : ''

				return array.slice(0, max).join(' ') + ellipsis
			}
		}
	}
</script>

<style>
	ul.folder-buttons {
		margin-bottom: 10px;
	}

	ul.folder-buttons li {
		display: inline;
		margin-right: 5px;
	}

	.folder-button {
		background: #fff;
		padding: 6px 10px;
	}

	.folder-button.current-folder {
		background: #666;
		color: #fff;
	}

	@media screen and (max-width:600px) {
		.group-library-folder-filter-dropdown .vs__dropdown-toggle,
		.group-library-folder-filter-dropdown .vs__selected-options {
			background: #666;
		}

		.group-library-folder-filter-dropdown .vs__selected {
			color: #fff;
		}

		.group-library-folder-filter-dropdown .vs__open-indicator {
			fill: #fff;
		}
	}
</style>
