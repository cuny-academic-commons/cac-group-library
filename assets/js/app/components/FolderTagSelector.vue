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
			label="code"
			:create-option="createOption"
			@option:created="onOptionCreated">

			<template #option="{code}">
				<div class="folder-tag-selector-option-content">
					{{ code }}
				</div>
			</template>

			<template #selected-option="{code}">
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
				for ( const folderName of foldersOfGroup ) {
					folders.push(
						{
							code: folderName,
							label: folderName,
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
					// value is an array of objects with code and label, or strings
					const folderNames = value ? value.map( item => {
						// Handle both object format {code, label} and string format
						return typeof item === 'string' ? item : (item.code || item.label || item)
					}) : []

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
			createOption( newTag ) {
				// Format new tags to match our expected structure
				return {
					code: newTag,
					label: newTag
				}
			},

			onOptionCreated( newOption ) {
				// When user creates a new tag by typing and pressing enter
				// Add it to the store's list of folders for the group
				// newOption is a string when created by taggable mode
				const folderName = typeof newOption === 'string' ? newOption : newOption.label
				this.$store.commit( 'addFolderToGroup', folderName )
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
