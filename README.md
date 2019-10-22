# ![Backpack with camouflage pattern](https://github.com/thebigsmileXD/Backpack/blob/master/resources/github/backpack.png) Backpack
[![Poggit-CI](https://poggit.pmmp.io/ci.badge/thebigsmileXD/Backpack/Backpack/master)](https://poggit.pmmp.io/ci/thebigsmileXD/Backpack) [![](https://poggit.pmmp.io/shield.state/Backpack)](https://poggit.pmmp.io/p/Backpack) [![](https://poggit.pmmp.io/shield.api/Backpack)](https://poggit.pmmp.io/p/Backpack)
***Get wearable backpacks for your PocketMine-MP server! Supports custom models and textures!***

## Features
- ✅ Supports custom geometry and skin files, for example made in [BlockBench](https://www.blockbench.net/)
- ⚔ Does not interfere with PvP and additionally stops the use of KillAura [_\[¹\]_](#Notes)
- 💻 Easily modifiable and configurable
- ⚖ Permission sets for every available design
- 💾 Minimal disk space usage due to savefiles in NBT format [_\[²\]_](#Notes)
- 📏 Automatically updates scale in relation to the player's scale. You have baby sized players on your server? No problem!
- 👻 Hidden when the player is invisible or dead
- 💎 27 extra slots to fill up with your precious goods
- 👨🏻‍💻 Simple to use commands - [`/backpack`](#Commands) Puts down the backpack to make it's contents accessible
- 💼 Can be opened by hitting the backpack entity
- 🔐 Only accessible by the owner of the backpack
- 🦸‍♂️ Immune to any impacts, as fire, explosions and player damage
- 👪 Easy way to add roleplaying and survival fun

## Screenshots
| | | |
|:---:|:---:|:---:|
|![Front screenshot](https://github.com/thebigsmileXD/Backpack/blob/master/resources/github/front.png)|![Back screenshot](https://github.com/thebigsmileXD/Backpack/blob/master/resources/github/back.png)|![Inventory screenshot](https://github.com/thebigsmileXD/Backpack/blob/master/resources/github/inventory.png)|

## Commands
| Command | Description | Permission |
|---|---|---|
| `/backpack` | `Toggle between wearing and accessing the backpack` | `backpack.command` |
| `/backpack create [design]` | `Creates a backpack for the sender` | `backpack.command` |
| `/backpack design <design>` | `Switches to another design` | `backpack.command` |
| `/backpack get` | `Gives an item that when dropped puts on the backpack` | `backpack.command` |

## Special permissions
By setting up permissions in the style of `backpack.type.FILENAME` you can make cool and exclusive designs available to specific users and groups.

Example for permissions:
 * For the file `default.json` the permission would result in `backpack.type.default`
 * By giving the `backpack.type` permission _all_ designs will be accessible

## Creating new models and textures
For creating the entities i suggest using [BlockBench](https://www.blockbench.net/), a free modeling program for Minecraft with included painting tools. It can also be ran in the browser and is mobile/touch friendly.

***Some things must be given attention to when creating those models:***
- I suggest importing a player model to have a visual preview of where you actually are putting the cubes at. You can toggle the visibility of the bones at any time, making it easy to tweak the positioning of cubes.
- **Only** the `body` bone/group **may** be used, since it is able to automatically align the backpack correctly, even when sneaking, swimming or elytra-flying. Cubes put into the `body` group to archive the best results.
- The pivot of the `body` bone/group must be at `[0, 24, 0]` to make sure the cubes align properly when sneaking
- Textures must be 64 * 32, 64 * 64 or 128 * 128 size. Other sizes won't render properly. Those are [supported dimensions of a player skin](https://github.com/pmmp/PocketMine-MP/blob/3.9.4/src/pocketmine/entity/Skin.php#L33-L37) (as of October 2019)
- BlockBench supports generating texture templates, which is a great time and headache savior, since it can also try to "compress" the texture file by putting smaller cube textures in the unused areas of bigger cubes.
- The geometry name must be `geometry.backpack.FILENAME`. The geometry file must be named `FILENAME.json`. The PNG texture file must be named `FILENAME.png`.

## Planned features
- [ ] Database support for cross server backpack saving
- [ ] Optional UI for choosing the design of the backpack (alongside the `/backpack design` command)
- [ ] Translations
- [ ] Restrictions for allowing the use only in a set of worlds
- [ ] Colored messages

## Notes
- To be able to run this plugin the `gd` extension must be installed and enabled. See [this link 🖥](https://forums.pmmp.io/threads/gd-on-php.6532/) or [this link 🐧](https://forums.pmmp.io/threads/how-to-install-gd-lib-in-php-binary.4372/) for information on how to enable it.
- This plugin utilizes the [InvMenu](https://github.com/Muqsit/InvMenu/) virion, which must be installed alongside [DEVirion](https://github.com/poggit/devirion/) when running from source
- Feel free to modify the plugin and contribute to the development (_as long as you give credit 😉_)! Pull requests are most welcome ☺

- _[¹]_ Simple KillAura hacks can be disturbed by this plugin if poorly coded. The hack client will have a hard time figuring out if it is actually targeting a Player or a Human entity (which are used in this plugin), and probably will attack the backpacks instead 👍🏻
- _[²]_ You can edit the NBT files via [NBTExplorer](https://github.com/jaquadro/NBTExplorer), [UniversalMinecraftEditor](https://www.universalminecrafteditor.com/) or similar programs with ease

## Credits
A plugin by XenialDan, 2019

Many thanks to @Muqsit for helping with the data saving and to @CortexPE for his amazing Commando virion