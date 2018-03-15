<template>
	<div class="form-autocomplete" style="width: 100%;" v-on-clickaway="closeDropdown">
		<!-- autocomplete input container -->
		<div class="form-autocomplete-input form-input" :class="is_focused">
			
			<!-- autocomplete chips -->
			<label class="chip" v-for="( account, index ) in to_be_activated">
				<img :src="getImg(account.img)" class="avatar avatar-sm" alt="{account.name}">
				{{account.name}}
				<a href="#" class="btn btn-clear" aria-label="Close" @click.prevent="removeToBeActivated(index)"
				   role="button"></a>
			</label>
			
			<!-- autocomplete real input box -->
			<input style="height: 1.0rem;" class="form-input" type="text" ref="search" v-model="search"
			       :placeholder="autocomplete_placeholder" @click="magic_flag = true" @focus="magic_flag = true"
			       @keyup="magic_flag = true" @keydown.8="popLast()" @keydown.38="highlightItem(true)"
			       @keydown.40="highlightItem()">
		</div>
		
		<!-- autocomplete suggestion list -->
		<ul class="menu" ref="autocomplete_results" :class="is_visible">
			<!-- menu list chips -->
			<li class="menu-item" v-for="( account, index ) in accounts" v-if="filterSearch(account)">
				<a href="#" @click.prevent="addToBeActivated(index)" @keydown.38="highlightItem(true)"
				   @keydown.40="highlightItem()">
					<div class="tile tile-centered">
						<div class="tile-icon">
							<img :src="getImg(account.img)" class="avatar avatar-sm" alt="{account.name}">
						</div>
						<div class="tile-content" v-html="markMatch(account.name, search)"></div>
					</div>
				</a>
			</li>
			<li v-if="has_results">
				<a href="#">
					<div class="tile tile-centered">
						<div class="tile-content"><i>Nothing found matching "{{search}}" ...</i></div>
					</div>
				</a>
			</li>
		</ul>
	</div>

</template>

<script>
	/* global ROP_ASSETS_URL */
	import {mixin as clickaway} from 'vue-clickaway'

	function containsObject(obj, list) {
		let i
		for (i = 0; i < list.length; i++) {
			if (list[i] === obj) {
				return true
			}
		}
		return false
	}

	module.exports = {
		name: 'service-autocomplete',
		mixins: [clickaway],
		props: ['accounts', 'to_be_activated', 'disabled', 'limit'],
		mounted() {

		},
		data: function () {
			return {
				search: '',
				highlighted: -1,
				no_results: false,
				magic_flag: false,
				account_def_img: ROP_ASSETS_URL + 'img/accounts_icon.jpg',
				toActivateCount: 0
			}
		},
		watch: {
			to_be_activated: function () {
				this.toActivateCount = this.to_be_activated.length
			}
		},
		computed: {
			is_focused: function () {
				return {
					'is-focused': this.magic_flag === true
				}
			},
			is_visible: function () {
				return {
					'd-none': this.magic_flag === false
				}
			},

			autocomplete_placeholder: function () {
				if (this.to_be_activated.length > 0) {
					return '';
				}
				return 'Accounts ...'
			},
			has_results: function () {
				let found = 0
				for (var account of this.accounts) {
					if (this.filterSearch(account)) {
						found++
					}
				}
				if (found) {
					return false
				}
				return true
			}
		},
		methods: {
			closeDropdown: function () {
				this.magic_flag = false
			},
			highlightItem: function (up = false) {
				if (up) {
					this.highlighted--
				} else {
					this.highlighted++
				}
				var size = this.$refs.autocomplete_results.children.length - 1
				if (size < 0) size = 0
				if (this.highlighted > size) this.highlighted = 0
				if (this.highlighted < 0) this.highlighted = size
				this.$refs.autocomplete_results.children[this.highlighted].firstChild.focus()
			},
			popLast: function () {
				if (this.search === '') {
					this.to_be_activated.pop()
					this.magic_flag = false
				}
			},
			markMatch: function (value, search) {
				var result = value
				if (value.toLowerCase().indexOf(search.toLowerCase()) !== -1 && search !== '') {
					var rex = new RegExp(search, 'ig')
					result = value.replace(rex, function (match) {
						return '<mark>' + match + '</mark>'
					})
				}
				return result
			},
			getImg(img) {
				if (img === '' || img === undefined || img === null) {
					return this.account_def_img
				}
				return img
			},
			filterSearch(element) {
				if (element.name.toLowerCase().indexOf(this.search.toLowerCase()) !== -1 || this.search === '') {
					if (element.active) {
						return false;
					}
					if (containsObject(element, this.to_be_activated)) {
						return false
					}
					return true
				}
				return false
			},
			addToBeActivated(index) {
				this.to_be_activated.push(this.accounts[index])
				this.$refs.search.focus()
				this.magic_flag = false
				this.search = ''
			},
			removeToBeActivated(index) {
				this.to_be_activated.splice(index, 1)
				this.$refs.search.focus()
				this.magic_flag = false
				this.search = ''
			}
		}
	}
</script>