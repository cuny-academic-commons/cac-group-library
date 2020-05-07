<template>
	<span class="group-library-filter-dropdown">
		<label
			v-bind:for="fieldId"
			class="screen-reader-only">
			{{title}}
		</label>

		<v-select
			v-bind:clearable="false"
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

					this.$store.commit( 'refresh' )
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
.v-select {
	display: inline-block;
	width: 200px;
}

@media screen and (max-width:600px) {
	.v-select {
		width: 49%;
	}
}
</style>
