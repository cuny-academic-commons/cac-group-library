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
				for ( var groupFolderSlug in foldersOfGroup ) {
					folders.push( { 'code': groupFolderSlug, label: foldersOfGroup[ groupFolderSlug ] } )
				}

				folders.push( { 'code': '_addNew', label: 'Add new folder' } )

				return folders
			},

			selected: {
				get() {
					const { folder } = this.$store.state.forms[ this.form ]

					if ( '_addNew' === folder ) {
						return { 'code': '_addNew', label: 'Add new folder' }
					}

					const { foldersOfGroup } = this
					if ( foldersOfGroup.hasOwnProperty( folder ) ) {
						return { 'code': folder, label: foldersOfGroup[ folder ] }
					}

					return folder
				},

				set( value ) {
					const theValue = null === value ? [] : value.code

					this.$store.commit(
						'setFormFieldValue',
						{
							field: 'folder',
							form: this.form,
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
