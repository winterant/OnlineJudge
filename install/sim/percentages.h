/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: percentages.h,v 1.10 2020-08-14 16:56:52 dick Exp $
*/

#include	"text.h"

extern void Add_To_Percentages(
    const struct text *txt0, const struct text *txt1, size_t size);
extern void Print_Percentages(void);
