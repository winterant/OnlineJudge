/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: add_run.h,v 1.8 2020-08-14 16:56:50 dick Exp $
*/

/*	Interface between front-end and back-end: all information about
	runs passes through Add_Run().  Its parameters are the two chunks,
	each identified by their struct text and the position of the common
	segment in Token_Array[], and the number of tokens in the common
	segment.

	The routine Add_Run() constructs a run with the given properties and
	adds it to 'runs.[ch]' or 'percentages.[ch]'.
*/

#include	<stdio.h>
#include	"text.h"

extern void Add_Run(
	struct text *txt0,		/* text of first chunk */
	size_t i0,			/* chunk position in Token_Array[] */
	struct text *txt1,		/* text of second chunk */
	size_t i1,			/* chunk position in Token_Array[] */
	size_t size			/* number of tokens in the chunk */
);
