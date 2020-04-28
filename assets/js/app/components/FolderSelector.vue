<template>
	<div class="folder-selector-dropdown">
		<v-select
			:id="inputId"
			v-model="selected"
			placeholder="Select folder"
			:options="opts">
		</v-select>
	</div>
</template>

<script>
	import vSelect from 'vue-select'
	import 'vue-select/dist/vue-select.css';

	export default {
		components: {
			vSelect
		},

		computed: {
			foldersOfGroup() {
				return this.$store.state.foldersOfGroup
			},

			opts() {
				let folders = []

				let theFolder
				const { foldersOfGroup } = this
				for ( var i in foldersOfGroup ) {
					theFolder = foldersOfGroup[ i ]
					folders.push( { 'code': theFolder.slug, label: theFolder.name } )
				}

				folders.push( { 'code': '_addNew', label: 'Add new folder' } )

				return folders
			},

			selected: {
				get() {
					const folders = this.$store.state.foldersExternalLink

					if ( folders.length === 0 ) {
						return ''
					}

					const theFolder = folders.shift()

					if ( '_addNew' === theFolder ) {
						return { 'code': '_addNew', label: 'Add new folder' }
					}

					const { foldersOfGroup } = this
					let theTerm
					for ( var i in foldersOfGroup ) {
						theTerm = foldersOfGroup[ i ]
						if ( theTerm.slug === theFolder ) {
							const retVal = { 'code': theTerm.slug, label: theTerm.name }
							return retVal
						}
					}

					return ''
				},

				set( value ) {
					const theValue = null === value ? [] : [ value.code ]

					this.$store.commit(
						'setAddNewFolders',
						{
							form: 'externalLink',
							value: theValue
						}
					)
				}
			}
		},

		props: {
			form: String,
			inputId: String
		}
	}
</script>

<style>
.folder-selector-dropdown .vs__dropdown-option:last-child {
	background: #f5f5f5;
	margin-top: 10px;
}

.folder-selector-dropdown .vs__dropdown-option:last-child:hover {
	background: #5897fb;
}
</style>
