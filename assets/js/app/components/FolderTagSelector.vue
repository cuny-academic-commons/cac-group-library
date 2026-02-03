<template>
	<div class="folder-tag-selector">
		<v-select
			:appendToBody="true"
			:id="inputId"
			v-model="selected"
			placeholder="Select or type to add tags"
			:options="opts"
			:multiple="true"
			:taggable="true"
			:closeOnSelect="false"
			@option:created="onOptionCreated">

			<template #option="{code, value}">
				<div class="folder-tag-selector-option-content">
					{{ code }}
				</div>
			</template>

			<template #selected-option="{code, value}">
				<span>{{ code }}</span>
			</template>
		</v-select>
	</div>
</template>

<script>
	import vSelect from 'vue-select'
	import 'vue-select/dist/vue-select.css';

	import FormValidation from '../mixins/FormValidation.js'

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

				const { foldersOfGroup } = this
				for ( var i in foldersOfGroup ) {
					folders.push(
						{
							code: foldersOfGroup[ i ],
							label: foldersOfGroup[ i ],
						}
					)
				}

				return folders
			},

			selected: {
				get() {
					const { folder } = this.$store.state.forms[ this.form ]

					// Handle both array and string formats for backwards compatibility
					let folderArray = []
					if ( Array.isArray( folder ) ) {
						folderArray = folder
					} else if ( folder && folder.length > 0 ) {
						folderArray = [ folder ]
					}

					const { foldersOfGroup } = this
					return folderArray.map( folderName => {
						return { code: folderName, label: folderName }
					})
				},

				set( value ) {
					// value is an array of objects with code and label
					const folderNames = value ? value.map( item => item.code ) : []

					this.$store.commit(
						'setFormFieldValue',
						{
							field: 'folder',
							form: this.form,
							value: folderNames
						}
					)
				}
			},
		},

		mixins: [
			FormValidation
		],

		methods: {
			onOptionCreated( newOption ) {
				// When user creates a new tag by typing and pressing enter
				// Add it to the store's list of folders for the group
				this.$store.commit( 'addFolderToGroup', newOption.label )
			}
		},

		props: {
			form: String,
			inputId: String
		}
	}
</script>

<style>
.folder-tag-selector .v-select {
	width: auto;
}

.folder-tag-selector .vs__dropdown-menu {
	z-index: 999;
}

.folder-tag-selector .vs__dropdown-option {
	padding: 0;
}

.folder-tag-selector .folder-tag-selector-option-content {
	padding: 3px 20px;
}

.folder-tag-selector .vs__selected-options {
	min-width: 300px;
}

/* Style for the tags/bubbles */
.folder-tag-selector .vs__selected {
	background-color: #f0f0f0;
	border: 1px solid #ddd;
	border-radius: 3px;
	color: #333;
	display: inline-flex;
	align-items: center;
	margin: 2px;
	padding: 2px 8px;
}

.folder-tag-selector .vs__deselect {
	fill: #999;
	margin-left: 4px;
}

.folder-tag-selector .vs__deselect:hover {
	fill: #333;
}
</style>
