# TwitterOAuth (namespaced copy)

Copy of [Codeinwp/twitteroauth](https://github.com/Codeinwp/twitteroauth) at commit
`9f8c5b8478e0845c137c91b86a98ba367f5e7695` (a fork of `abraham/twitteroauth`), with the
namespace renamed from `Abraham\TwitterOAuth` to `Rop_Vendor\TwitterOAuth`.

Other plugins bundle their own `Abraham\TwitterOAuth` with different method signatures.
Because every Composer autoloader registers globally, the copies were mixed at runtime and
crashed (see issue #1128). Owning the namespace keeps this plugin's copy to itself.

To update: copy `src/` from the fork and run
`sed -i 's/Abraham\\TwitterOAuth/Rop_Vendor\\TwitterOAuth/g'` over it.
MIT licensed, see LICENSE.md.
