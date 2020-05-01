<template>
	<div class="folder-selector-dropdown">
		<v-select
			:appendToBody="true"
			:id="inputId"
			v-model="selected"
			placeholder="Select folder"
			:options="opts">

			<template #option="{code, value}">
				<div v-if="'_addNew' === code" class="add-new-folder-option folder-selector-option-content">
					<span class="dashicons dashicons-plus"></span> Add new folder
				</div>

				<div v-else class="folder-selector-option-content">
					{{ code }}
				</div>
			</template>

			<template #selected-option="{code, value}">
				<span v-if="'_addNew' === code" class="add-new-folder-option-selected">
					<span class="dashicons dashicons-plus"></span> Add new folder
				</span>

				<span v-else>
					{{ code }}
				</span>
			</template>
		</v-select>

		<div 
			:class="addNewFolderContainerClass"
			v-if="showNewFolder">

			<label class="screen-reader-text" for="add-new-folder-input">Enter new folder title</label>
			<input
				v-model="newFolderTitle"
				id="add-new-folder-input"
				name="add-new-folder-input"
				placeholder="Enter new folder title"
				v-on:blur="setFieldVisited()"
				type="text"
			/>

			<p
				class="field-error"
				v-if="validationError"
			>{{ validationError }}</p>
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

		computed: {
			addNewFolderContainerClass() {
				let classes = [ 'add-new-folder-container' ]

				if ( this.validationError ) {
					classes.push( 'has-error' )
				}

				return classes.join( ' ' )
			},

			foldersOfGroup() {
				return this.$store.state.foldersOfGroup
			},

			newFolderTitle: {
				get() {
					return this.$store.state.forms[ this.form ].newFolderTitle
				},

				set( value ) {
					this.$store.commit(
						'setFormFieldValue',
						{
							field: 'newFolderTitle',
							form: this.form,
							value
						}
					)
				}
			},

			opts() {
				let folders = []

				let theFolder
				const { foldersOfGroup } = this
				for ( var i in foldersOfGroup ) {
					folders.push(
						{
							code: foldersOfGroup[ i ],
							label: foldersOfGroup[ i ],
						}
					)
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
					if ( -1 !== foldersOfGroup.indexOf( folder ) ) {
						return { 'code': folder, label: folder }
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
			},

			showNewFolder() {
				return this.selected.code === '_addNew'
			},

			validationError() {
				const error = this.getFieldError( 'add-new-folder-input' )

				if ( error.length > 0 ) {
					return error
				}

				return ''
			},
		},
		
		mixins: [
			FormValidation
		],

		methods: {
			setFieldVisited() {
				this.$store.commit(
					'setFieldHasBeenVisited',
					{
						fieldName: 'add-new-folder-input',
						formName: this.form,
					}
				)
			},
		},

		props: {
			form: String,
			inputId: String
		}
	}
</script>

<style>
.folder-selector-dropdown .vs__dropdown-menu {
	z-index: 999;
}

.folder-selector-dropdown .vs__dropdown-option {
	padding: 0;
}

.folder-selector-dropdown .folder-selector-option-content {
	padding: 3px 20px;
}

.folder-selector-dropdown .vs__dropdown-option .add-new-folder-option {
	background: #f5f5f5;
	margin-top: 10px;
}

.folder-selector-dropdown .vs__dropdown-option:hover .add-new-folder-option {
	background: #5897fb;
}

.add-new-folder-option {
	display: block;
	height: 30px;
	line-height: 30px;
	width: 100%;
}

.add-new-folder-option .dashicons {
	line-height: 30px;
}

.add-new-folder-container {
	background: #f5f5f5;
	margin-top: 10px;
	padding: 8px 12px;
}

.add-new-folder-container input {
	width: 50%;
}

</style>
