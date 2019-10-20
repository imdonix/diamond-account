**Diamond Account Documentation**

## [Database tables:]()

### [User]()

[
id | name | username | password | email | verified | lastgame | invited | coin
| exp | loginkey

- name, username: engabc & number [varchar
20]

- pass: hash MD5 [varchar 32]

- verified: account verified (bool)

- lastgame: that game what this player played,
set when login (game/id) (-1 null)

- invited : that player who invited to play
(player/id)

-coin: premium currency

-exp: | level = 50 * (level) * (level * 0.1f) |
  

(exp packs | T1 - 10 | T2 - 35 | T3 - 50 | T4 - 100 )

[-Game]()

[
id | name | apikey | reqcounter | inconlink | verison | filelink |
ismobilegame]

### [Friends]()

[
id | pfrom(id) | pto(id) | accepted(bool) ]

[Connection string:]() mysql://s0gj9ytleew4g7ax:r5r187ankudkbk9a@q7cxv1zwcdlw7699.chr7pe7iynqr.eu-west-1.rds.amazonaws.com:3306/jamyk9cr08h9qo69

## [MasterKey:]()

e268443e43d93dab7ebef303bbe9642f

## [Errors]()

E0 = database not aviable
E1 = bad commad | Non GET type request
E2 = bad arguments
E3 = bad apikey
E4 = bad loginkey
E5 = wrong pass/username
E6 = Unable to handle friend request
E71 = (Register) username exist
E72 = (Register) in game name exist
E73 = (Register) email exist
E74 = (Register) encryped password
E9 = function has no result

## [Output]()

[Text/]
(data)&(data)@(sidedata)&(data)... 

## [Endpoint]()

https://diamond-account.herokuapp.com/api.php

** **

## [API [ http://host/api.php ]]()

### [Main:]()

#### [Login:]()

/api.php?type=login&un=(username)&ps=(password)&apikey=(apikey)

Input: Username, Password,
APIkey

Out: [text/] loginkey
(random generated MD5 hashcode)

#### [Register]():

/api.php?type=register&un=(username)&ig=(ingamename)&ps=(password)&email=(email)&apikey=(apikey)

Input: Username, In Game
Name, Email, Password(RAW), APIkey

Password = ABC + 1-9

Out: [text/] loginkey
(random generated MD5 hashcode)

#### [API info]()

/api.php?type=api&apikey=(apikey)

Input: APIkey

Out: [text/] (api version)

#### [Own Account
info:]()

/api.php?type=info&loginkey=(loginkey)&apikey=(apikey)

Input: Loginkey, APIkey

Out:
(id)&(name)&(username)&(email)&(verified)

&(lastgame)&(invited)&(coin)&(exp)

#### [Find Account info by ID]()

/api.php?type=findbyid&id=(id)&apikey=(apikey)

 Input: ID(Player), APIkey

 Out:
(id)&(name)&(username)&(email)&(verified)

&(lastgame)&(invited)&(coin)&(exp)

#### [Find Account
info by Name:]()


/api.php?type=findbyname&name=(id)&loginkey=(loginkey)&apikey=(apikey)


Input: name(Player),
Loginkey, APIkey

Out:(id)&(name)&(username)&(email)&(verified)

&(lastgame)&(invited)&(coin)&(exp)

 

#### [Game info:]()

/api.php?type=game&id=(id)&apikey=(apikey)


Input: gameid, APIkey

Out: [text/]
(game/id)&(game/name)&(game/reqcounter)

#### [Earn Exp:]()

/api.php?type=earnexp&loginkey=(loginkey)&earn=(type)&apikey=(apikey)


Input: loginkey,
earn(1,2,3,4), APIkey

Out: [text/] (newexp)

#### [Get all
game:]()

/api.php?type=games&apikey=(apikey)

Input: APIkey

Out: [text/]
(game/id)&(game/id)&(game/id)

### [FriendSystem:]()

#### [Send friend
request:]()

/api.php?type=Fsend&id=(id)&loginkey=(loginkey)&apikey=(apikey)

Input: ID(Player), Loginkey,
APIkey

Out:Succes

#### [Accept
friend request:]()

/api.php?type=Faccept&id=(id)&loginkey=(loginkey)&apikey=(apikey)

Input: ID(friends/ID),
Loginkey, APIkey

Out:Succes 

#### [Delete
friend contact]()

/api.php?type=Faccept&id=(id)&loginkey=(loginkey)&apikey=(apikey)

Input: ID(friends/ID),
Loginkey, APIkey

Out: Succes 

#### [Get pending
requests:]()

/api.php?type=Fpendings&id=(id)&loginkey=(loginkey)&apikey=(apikey)

Input: Loginkey, APIkey

Out:(friends/id)@(friends/pfrom)@(friends/pto)&(friends/id)...

#### [Get Friends:]()


/api.php?type=friends&loginkey=(loginkey)&apikey=(apikey) 

Input: Loginkey, APIkey

Out:
(friends/id)@(friends/pfrom)@(friends/pto)&(friends/id) 
