/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: runs.h,v 1.18 2020-08-14 16:56:53 dick Exp $
*/

/*	Although all other segments of data in this program are described by
	giving the position of the first in the segment and that of the
	first not in the segment (so the size is the difference of the two),
	a `chunk' is given by first and last. This is done because later on we
	are interested in the actual position of the last token of it, and
	the position of the first token not in the segment gives no
	indication about that.
*/

#include	"text.h"

struct chunk {
	/* a chunk of text */
	const struct text *ch_text;	/* pointer to the file */
	struct position ch_first;	/* first in chunk */
	struct position ch_last;	/* last in chunk */
};

struct run {				/* a 'run' of coincident tokens */
	struct run *rn_next;
	struct chunk rn_chunk0;		/* chunk in left file */
	struct chunk rn_chunk1;		/* chunk in right file */
	size_t rn_size;
};

extern void Add_To_Runs(
    struct text *txt0, size_t i0, struct text *txt1, size_t i1, size_t size
);
extern struct run *Sorted_Runs(void);
extern struct run *Unsorted_Runs(void);
extern void Discard_Runs(void);
