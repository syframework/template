<?php if (isset($VAR)) echo $VAR; ?><?php if (isset($BLOCK)): foreach ($BLOCK as $b): echo $b['VAR']; endforeach; endif; ?>