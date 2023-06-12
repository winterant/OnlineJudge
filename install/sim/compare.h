/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: compare.h,v 1.6 2020-08-14 16:56:50 dick Exp $
*/

/*	Compares each new text to the appropriate texts.
	Stores the runs found by passing them to Add_Run().
	Runs contain references to positions in the input files.
*/

extern void Compare_Files(void);
