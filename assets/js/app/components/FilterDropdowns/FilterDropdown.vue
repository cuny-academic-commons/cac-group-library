<template>
	<span>
		<label
			v-bind:for="fieldId"
			class="screen-reader-only">
			{{title}}
		</label>

		<v-select
			v-bind:id="fieldId"
			v-model="selected"
			:options="opts">
		</v-select>
	</span>
</template>

<script>
	import vSelect from 'vue-select'
	import 'vue-select/dist/vue-select.css';

	export default {
		components: {
			vSelect
		},

		computed: {
			fieldId() {
				return 'filter-' + this.name
			},

			selected: {
				get: function() {
					return this.getCurrentCallback()
				},

				set: function( payload ) {
					this.setCurrentCallback( payload )
				}
			},
		},

		props: {
			getCurrentCallback: Function,
			name: String,
			opts: Array,
			setCurrentCallback: Function,
			title: String,
		}
	}
</script>

<style>
</style>
