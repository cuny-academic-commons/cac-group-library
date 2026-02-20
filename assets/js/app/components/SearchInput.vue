<template>
	<div class="library-search">
		<input
			class="library-search-input"
			type="text"
			:placeholder="placeholderText"
			:style="backgroundStyles"
			v-on:focus="onFocus()"
			v-model="currentSearchTerm"
		/>

		<span
			class="dashicons dashicons-no-alt search-input-click-to-close"
			v-if="isSearchExpanded"
			v-on:click="onCloseClick()"
		><span class="screen-reader-text">Click to cancel search</span></span>
	</div>
</template>

<script>
	export default {
		computed: {
			backgroundStyles() {
				const iconUrl = require('../../../img/search.svg');

				if (this.isSearchExpanded) {
					return {
						backgroundImage: `url(${iconUrl})`,
					};
				}

				return {
					backgroundImage: `url(${iconUrl})`,
					opacity: '.75'
				};
			},

			currentSearchTerm: {
				get() {
					return this.$store.state.route.query.hasOwnProperty( 'searchTerm' ) ? decodeURIComponent( this.$store.state.route.query.searchTerm ) : ''
				},

				set( value ) {
					const newQuery = Object.assign( {}, this.$router.query, {
						searchTerm: value
					} )
					this.$router.push({
						path: '/',
						query: newQuery
					})

					this.$store.commit( 'refreshFilteredItemIds' )
				}
			},

			isSearchExpanded: {
				get() {
					return this.$store.state.isSearchExpanded
				},

				set( value ) {
					this.$store.commit(
						'setIsSearchExpanded',
						{ value }
					)

					this.$store.commit(
						'setShowDescriptions',
						{ value }
					)

					// Required to trigger pagination.
					this.$store.commit( 'refreshFilteredItemIds' )
				}
			},

			placeholderText() {
				return 'Search the Library'
			}
		},

		created() {
			if ( this.currentSearchTerm.length > 0 ) {
				this.isSearchExpanded = true
			}
		},

		methods: {
			onCloseClick( event ) {
				this.currentSearchTerm = ''
				this.isSearchExpanded = ''

				this.$store.commit( 'refreshFilteredItemIds' )

				return false
			},

			onFocus() {
				// Don't re-clear filters if already expanded or if there's search text.
				if ( this.isSearchExpanded || this.currentSearchTerm.length > 0 ) {
					return
				}

				this.$router.push({
					path: '/',
					query: {}
				})

				this.isSearchExpanded = true
			}
		}
	}
</script>

<style>
.library-search {
	position: relative;
	transition: width .5s ease-in-out;
	width: 200px;
}

.search-input-click-to-close {
	cursor: pointer;
	position: absolute;
	right: 8px;
	top: 50%;
	transform: translateY( -50% );
}

input[type="text"].library-search-input {
	background-repeat: no-repeat;
	background-position: center left 8px;
	background-size: 16px;
	padding-left: 36px;
	width: calc(100% - 48px);
	border: 1px solid #cecece;
	border-radius: 5px;
	margin-bottom: 0;
}
</style>
