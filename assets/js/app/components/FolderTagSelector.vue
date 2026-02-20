<template>
	<div class="folder-tag-selector">
		<v-select
			:appendToBody="false"
			:id="inputId"
			v-model="selected"
			placeholder="Type to create or select tags"
			:options="opts"
			:multiple="true"
			:taggable="true"
			:closeOnSelect="false"
			label="code"
			:create-option="createOption"
			@option:created="onOptionCreated"
			@search:focus="onFocus"
			@search:blur="onBlur"
			aria-label="Tag selector - Type to create new tags or select from existing"
			role="combobox"
			aria-multiselectable="true">

			<template #option="{code}">
				<div class="folder-tag-selector-option-content">
					{{ code }}
				</div>
			</template>

			<template #selected-option="{code}">
				<span>{{ code }}</span>
			</template>

			<template #search="{attributes, events}">
				<input
					class="vs__search"
					v-bind="attributes"
					v-on="events"
					:placeholder="searchPlaceholder"
					aria-label="Search or create tags"
				/>
			</template>
		</v-select>
		<div
			v-if="showHelperText"
			class="folder-tag-selector-helper"
			role="status"
			aria-live="polite">
			Press Enter to create new tag
		</div>
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

		data() {
			return {
				isFocused: false
			}
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

			searchPlaceholder() {
				// Show different placeholder when focused vs not focused
				if ( this.isFocused ) {
					return 'Type tag name'
				}
				return this.selected.length === 0 ? 'Type to create or select tags' : ''
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

			showHelperText() {
				// Show helper text when focused and user is typing
				return this.isFocused
			}
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

			onFocus() {
				this.isFocused = true
			},

			onBlur() {
				this.isFocused = false
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
.folder-tag-selector {
	position: relative;
}

.folder-tag-selector .v-select {
	width: 100%;
}

/* Fix dropdown position when input wraps to multiple lines */
.folder-tag-selector .vs__dropdown-menu {
	z-index: 999;
	position: absolute;
	top: 100%;
	left: 0;
	right: 0;
	margin-top: 2px;
}

.folder-tag-selector .vs__dropdown-option {
	padding: 0;
}

.folder-tag-selector .folder-tag-selector-option-content {
	padding: 3px 20px;
}

.folder-tag-selector .vs__selected-options {
	min-width: 300px;
	flex-wrap: wrap;
}

/* Style for the tags/bubbles - reduced right padding */
.folder-tag-selector .vs__selected {
	background-color: #f0f0f0;
	border: 1px solid #ddd;
	border-radius: 3px;
	color: #333;
	display: inline-flex;
	align-items: center;
	margin: 2px;
	padding: 2px 4px 2px 8px; /* Reduced right padding from 8px to 4px */
}

/* Adjust deselect button spacing */
.folder-tag-selector .vs__deselect {
	fill: #999;
	margin-left: 6px;
	padding: 0 2px !important;
}

.folder-tag-selector .vs__deselect:hover {
	fill: #333;
}

/* Helper text styling */
.folder-tag-selector-helper {
	font-size: 12px;
	color: #666;
	font-style: italic;
	margin-top: 4px;
	padding-left: 2px;
}

/* Improve search input placeholder styling */
.folder-tag-selector .vs__search::placeholder {
	color: #999;
	font-style: italic;
}

/* Better focus states for accessibility */
.folder-tag-selector .vs__dropdown-toggle {
	border-color: #ddd;
	padding-top: 4px;
}

.folder-tag-selector .vs__dropdown-toggle:focus-within {
	border-color: #5897fb;
	box-shadow: 0 0 0 1px #5897fb;
	outline: none;
}

/* Improve keyboard focus visibility */
.folder-tag-selector .vs__dropdown-option--highlight {
	background: #5897fb;
	color: #fff;
}

/* Screen reader only text for accessibility */
.folder-tag-selector .sr-only {
	position: absolute;
	width: 1px;
	height: 1px;
	padding: 0;
	margin: -1px;
	overflow: hidden;
	clip: rect(0, 0, 0, 0);
	white-space: nowrap;
	border-width: 0;
}
</style>
