<?php

namespace GCXAuthz {
	interface Processor {
		public function process(Object\User $user, $namespaces, $commands);
	}
}
