/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: hash.h,v 1.8 2020-08-14 16:56:50 dick Exp $
*/

/*	Creating and consulting forward_reference[], used to speed up
	the Longest Substring Algorithm.
*/

#include	<stdio.h>

extern void Make_Forward_References(void);
extern void Free_Forward_References(void);
/* with circularity check: */
extern size_t Forward_Reference(size_t i, size_t i0);
