FOLLOWING TASK WILL TEST ALL FEATURES :<br />
---------------------------------------------<br />
simple variable output of var : [[ var ]]<br />
var + 3 = [[ var + 3 ]]<br />
test boolean : [@ if var not ~true @]
<div style="font-style:italic">Ok , boolean success</div>
[@ end @]
constant : [[ ~TEST_CONST ]]<br />
array element : [[ array.key ]]<br />
class static property : [[ Test::test ]]<br />
class constant : [[ Test~TEST_CONST ]]<br />
number : [[ 5 ]]<br />
string : [[ "hehe" ]]<br />
function : [[ function:md5("a") ]]<br />
object method : [[ obj:md5("a") ]]<br />
class static method : [[ Test::md5("a") ]]<br />
test changing value of var [@ var to 3 @] , now its value is : [[ var ]]<br />
--- Some syntaxs<br />
[@ each array as k @]
<div style="font-style:italic;">This runs for each element of array , current element value is [[ k ]]</div>
[@ end @]
Test if else <br />
[@ if var is 3 @]
<div style="font-style:italic;">Ok , value of var is 3</div>
[@ else @]
<div style="font-style:italic;">No , value of var is not 3</div>
[@ end @]
Test value <br />
[@ value var ? 5 @]
<div style="font-style:italic;">Ok , value of var is 5</div>
[@ default @]
<div style="font-style:italic;">No , value of var is not 5</div>
[@ end @]
Test type <br />
[@ type var ? "string" @]
<div style="font-style:italic;">Ok , type of var is string</div>
[@ default @]
<div style="font-style:italic;">No , type of var is not string</div>
[@ end @]
--- Test raw php<br />
[@raw echo "THIS IS PHP CODE"; @]<br />
--- Test extending<br />
[@ extends extended.tpl @]